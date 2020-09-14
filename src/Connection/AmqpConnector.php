<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Connection;


use Denismitr\LaravelMQ\Exception\ConnectionException;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class AmqpConnector implements Connector
{
    /**
     * @inheritDoc
     */
    public function connect(array $config): AbstractConnection
    {
        /** @var AbstractConnection $connection */
        $connection = Arr::get($config, 'type', AMQPLazyConnection::class);

        // disable heartbeat when not configured, so long-running tasks will not fail
        $config = Arr::add($config, 'heartbeat', 0);

        try {
            return $connection::create_connection(
                Arr::shuffle(Arr::get($config, 'hosts', [])),
                $this->filter(Arr::get($config, 'options', []))
            );
        } catch (\Throwable $t) {
            throw ConnectionException::from($t);
        }
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