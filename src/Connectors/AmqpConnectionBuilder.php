<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Connectors;


use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConnectionException;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AbstractConnection;
use ReflectionMethod;
use Throwable;

class AmqpConnectionBuilder
{
    /**
     * @var string
     */
    private $connectionClass;
    /**
     * @var array
     */
    private $hosts;
    /**
     * @var array
     */
    private $options;

    /**
     * @return AbstractConnection
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function build(): AbstractConnection
    {
        $cls = $this->connectionClass;
        $hosts = Arr::shuffle($this->hosts);
        $options = $this->filter($this->options);

        try {
            $rm = new ReflectionMethod($cls, 'create_connection');
        } catch (Throwable $t) {
            throw ConfigurationException::invalidConnectionClass($cls, $t->getMessage());
        }

        if ( ! $rm->isStatic()) {
            throw ConfigurationException::invalidConnectionClass($cls, "no static method create_connection");
        }

        try {
            return $cls::create_connection($hosts, $options);
        } catch (Throwable $t) {
            throw ConnectionException::from($t);
        }
    }

    public function withConnectionClass(string $connectionClass): AmqpConnectionBuilder
    {
        $this->connectionClass = $connectionClass;
        return $this;
    }

    public function withHosts(array $hosts): AmqpConnectionBuilder
    {
        $this->hosts = $hosts;
        return $this;
    }

    public function withOptions(array $options): AmqpConnectionBuilder
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Recursively filter only null values.
     *
     * @param array $array
     * @return array
     */
    private function filter(array $array): array
    {
        foreach ($array as $k=>&$v) {
            if (\is_array($v)) {
                $v = $this->filter($v);
                continue;
            }

            if (\is_null($v)) {
                unset($v[$k]);
                continue;
            }
        }

        return $array;
    }
}