<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


use Denismitr\LaravelMQ\Connectors\Connector;
use Denismitr\LaravelMQ\Connectors\Factory;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConnectionException;

class DefaultBroker implements Broker
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Factory
     */
    private $factory;

    private static $connectors = [];

    /**
     * DefaultBroker constructor.
     * @param Factory $factory
     * @param array $config
     */
    public function __construct(Factory $factory, array $config)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * @param string $connection
     * @return Connection
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function connection(string $connection): Connection
    {
        $connector = $this->getConnectorFor($connection);

        return $connector->connect($this->config['connections'][$connection] ?? []);
    }

    /**
     * @param string $connection
     * @return Connector
     * @throws ConfigurationException
     */
    private function getConnectorFor(string $connection): Connector
    {
        if ( ! isset(static::$connectors[$connection])) {
            if ( ! isset($this->config['connections'])) {
                throw ConfigurationException::connectionsNotFound();
            }

            $connections = $this->config['connections'];
            if ( ! isset($connections[$connection]) || ! \is_array($connections[$connection])) {
                throw ConfigurationException::connectionNotFound($connection);
            }

            if ( ! isset($connections[$connection]['driver'])) {
                throw ConfigurationException::driverNotFound();
            }

            $driver = $connections[$connection]['driver'];
            $connector = $this->factory->make($driver);
            static::$connectors[$connection] = $connector;
        }

        return static::$connectors[$connection];
    }
}