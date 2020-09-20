<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Connectors;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Exception\ConfigurationException;

class Factory
{
    public function make(string $driver): Connector
    {
        if (strtolower($driver) === 'amqp' || strtolower($driver) === 'rabbitmq') {
            return new AmqpConnector(new AmqpChannelIdProvider());
        }

        throw ConfigurationException::invalidDriver($driver);
    }

    public function drivers(): array
    {
        return ['amqp', 'rabbitmq'];
    }
}