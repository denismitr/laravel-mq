<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
use Denismitr\LaravelMQ\Broker\Broker;
use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Broker\DefaultBroker;
use Denismitr\LaravelMQ\Connectors\Factory;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Tests\BaseTestCase;

class DefaultBrokerTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_create_amqp_connection()
    {
        $broker = new DefaultBroker(
            new Factory(),
            config('mq')
        );

        $this->assertInstanceOf(Broker::class, $broker);

        $connection = $broker->connection('default');
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(AmqpConnection::class, $connection);
    }

    /**
     * @test
     */
    public function it_will_throw_on_invalid_connection_name()
    {
        $broker = new DefaultBroker(
            new Factory(),
            config('mq')
        );

        $this->assertInstanceOf(Broker::class, $broker);

        $this->expectException(ConfigurationException::class);
        $broker->connection('non-existent');
    }
}