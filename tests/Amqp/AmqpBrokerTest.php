<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpBroker;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
use Denismitr\LaravelMQ\Broker\Broker;
use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Connection\AmqpConnector;
use Denismitr\LaravelMQ\Tests\BaseTestCase;

class AmqpBrokerTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_create_amqp_connection()
    {
        $broker = new AmqpBroker(new AmqpConnector(), new AmqpChannelIdProvider(), config('mq'));
        $this->assertInstanceOf(Broker::class, $broker);

        $connection = $broker->connection('default');
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(AmqpConnection::class, $connection);
    }
}