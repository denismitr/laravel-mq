<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Connectors;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConnectionException;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class AmqpConnector implements Connector
{
    /**
     * @var AmqpChannelIdProvider
     */
    private $idProvider;

    /**
     * AmqpConnector constructor.
     * @param AmqpChannelIdProvider $idProvider
     */
    public function __construct(AmqpChannelIdProvider $idProvider)
    {
        $this->idProvider = $idProvider;
    }

    /**
     * @inheritDoc
     */
    public function connect(array $config): Connection
    {
        /** @var AbstractConnection $connection */
        $connection = Arr::get($config, 'connection', AMQPLazyConnection::class);

        // disable heartbeat when not configured, so long-running tasks will not fail
        $config = Arr::add($config, 'params.options.heartbeat', 0);

        $targets = Arr::get($config, $key = "targets", null);
        if ( ! \is_array($targets)) {
            throw ConfigurationException::optionNotFound($key, "targets configuration");
        }

        $sources = Arr::get($config, $key = "sources", null);
        if ( ! \is_array($sources)) {
            throw ConfigurationException::optionNotFound($key, "sources configuration");
        }

        $params = Arr::get($config, $key = "params", null);
        if (! $params) {
            throw ConfigurationException::optionNotFound($key, "connection params");
        }

        try {
            $connection = $connection::create_connection(
                Arr::shuffle(Arr::get($config, 'params.hosts', [])),
                $this->filter(Arr::get($config, 'params.options', []))
            );
        } catch (\Throwable $t) {
            throw ConnectionException::from($t);
        }

        return new AmqpConnection(
            $connection,
            $this->idProvider,
            $targets,
            $sources
        );
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