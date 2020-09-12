<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Denismitr\LaravelMQ\Broker\Broker;
use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Connection\AmqpConnector;
use Denismitr\LaravelMQ\Connection\Connector;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConnectionException;
use Illuminate\Support\Arr;

class AmqpBroker implements Broker
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var array
     */
    private $config;

    private static $connections = [];

    /**
     * AmqpBroker constructor.
     * @param AmqpConnector $connector
     * @param array $config
     */
    public function __construct(AmqpConnector $connector, array $config)
    {
        $this->connector = $connector;
        $this->config = $config;
    }

    /**
     * @param string $connection
     * @return Connection
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function connection(string $connection): Connection
    {
        $targets = Arr::get($this->config, $key = "$connection.targets", null);
        if ( ! $targets) {
            throw ConfigurationException::optionNotFound($key, "targets configuration");
        }

        $sources = Arr::get($this->config, $key = "$connection.sources", null);
        if ( ! $sources) {
            throw ConfigurationException::optionNotFound($key, "sources configuration");
        }

        if ( ! isset(static::$connections[$connection])) {
            $params = Arr::get($this->config, $key = "$connection.params", null);
            if (! $params) {
                throw ConfigurationException::optionNotFound($key, "connection params");
            }

            static::$connections[$connection] = $this->connector->connect($params);
        }

        return new AmqpConnection(static::$connections[$connection], $targets, $sources);
    }
}