<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests;


use Denismitr\LaravelMQ\Connectors\AmqpConnector;
use Denismitr\LaravelMQ\Connectors\Connector;
use Denismitr\LaravelMQ\Connectors\Factory;
use Denismitr\LaravelMQ\Exception\ConfigurationException;

class ConnectorFactoryTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_create_rabbitmq_connector()
    {
        $factory = new Factory();
        $connector = $factory->make('rabbitmq');

        $this->assertInstanceOf(AmqpConnector::class, $connector);
        $this->assertInstanceOf(Connector::class, $connector);
    }

    /**
     * @test
     */
    public function it_will_throw_on_invalid_driver()
    {
        $factory = new Factory();
        $this->expectException(ConfigurationException::class);
        $factory->make('nonexistent');
    }
}