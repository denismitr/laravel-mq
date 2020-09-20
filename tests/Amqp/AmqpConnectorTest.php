<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Connectors\AmqpConnectionBuilder;
use Denismitr\LaravelMQ\Connectors\AmqpConnector;
use Denismitr\LaravelMQ\Tests\BaseTestCase;
use Mockery as m;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class AmqpConnectorTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_create_a_default_connection()
    {
        $config = config('mq.connections.default');

        $builderMock = m::mock(AmqpConnectionBuilder::class);
        $connectionMock = m::mock(AbstractConnection::class);

        $connector = new AmqpConnector($builderMock, new AmqpChannelIdProvider());

        $builderMock->expects('withHosts')->once()->andReturnSelf();
        $builderMock->expects('withOptions')->once()->andReturnSelf();
        $builderMock->expects('withConnectionClass')->once()->with(AMQPLazyConnection::class)->andReturnSelf();
        $builderMock->expects('build')->once()->andReturn($connectionMock);

        $connection = $connector->connect($config);

        $this->assertInstanceOf(AmqpConnection::class, $connection);
        $this->assertInstanceOf(Connection::class, $connection);
    }
}