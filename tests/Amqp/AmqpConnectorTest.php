<?php


namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
use Denismitr\LaravelMQ\Connectors\AmqpConnector;
use Denismitr\LaravelMQ\Tests\BaseTestCase;

class AmqpConnectorTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_create_a_default_connection()
    {
        $config = config('mq.connections.default');

        $connector = new AmqpConnector(new AmqpChannelIdProvider());

        $connection = $connector->connect($config);

        $this->assertInstanceOf(AmqpConnection::class, $connection);
    }
}