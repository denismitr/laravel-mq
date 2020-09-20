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
     * @var AmqpConnectionBuilder
     */
    private $builder;

    /**
     * AmqpConnector constructor.
     * @param AmqpConnectionBuilder $builder
     * @param AmqpChannelIdProvider $idProvider
     */
    public function __construct(AmqpConnectionBuilder $builder, AmqpChannelIdProvider $idProvider)
    {
        $this->idProvider = $idProvider;
        $this->builder = $builder;
    }

    /**
     * @inheritDoc
     */
    public function connect(array $config): Connection
    {
        /** @var AbstractConnection $connection */
        $connectionClass = Arr::get($config, 'connection_class', AMQPLazyConnection::class);

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

        $connection = $this
            ->builder
            ->withOptions(Arr::get($params, 'options', []))
            ->withHosts(Arr::get($params, 'hosts', []))
            ->withConnectionClass($connectionClass)
            ->build()
        ;

        return new AmqpConnection(
            $connection,
            $this->idProvider,
            $targets,
            $sources
        );
    }
}