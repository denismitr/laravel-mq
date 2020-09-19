<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpTarget;
use Denismitr\LaravelMQ\Broker\Target;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Tests\BaseTestCase;
use Denismitr\LaravelMQ\Broker\Message;
use Mockery as m;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpConnectionTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_instantiate_target_from_name()
    {
        $connMock = m::mock(AbstractConnection::class);

        $amqpConn = new AmqpConnection($connMock, new AmqpChannelIdProvider(), [
            'some-target' => [
                'exchange' => 'some-exchange',
                'routing_key' => 'some-routing-key'
            ]
        ], []);

        $target = $amqpConn->target('some-target');

        $this->assertInstanceOf(Target::class, $target);
        $this->assertInstanceOf(AmqpTarget::class, $target);
    }

    /**
     * @test
     */
    public function it_caches_target_by_name()
    {
        $connMock = m::mock(AbstractConnection::class);

        $amqpConn = new AmqpConnection($connMock, new AmqpChannelIdProvider(), [
            'some-target' => [
                'exchange' => 'some-exchange',
                'routing_key' => 'some-routing-key'
            ],
            'another-target' => [
                'exchange' => 'another-exchange',
                'routing_key' => 'another-routing-key'
            ]
        ], []);

        $targetA = $amqpConn->target('some-target');
        $targetB = $amqpConn->target('some-target');
        $targetC = $amqpConn->target('another-target');

        $this->assertInstanceOf(Target::class, $targetA);
        $this->assertInstanceOf(AmqpTarget::class, $targetB);
        $this->assertSame($targetA, $targetB);

        $this->assertInstanceOf(Target::class, $targetC);
        $this->assertInstanceOf(AmqpTarget::class, $targetC);
        $this->assertNotSame($targetA, $targetC);
    }

    /**
     * @test
     */
    public function it_will_throw_exception_on_invalid_target_name()
    {
        $this->expectException(ConfigurationException::class);

        $connMock = m::mock(AbstractConnection::class);
        $idProviderMock = m::mock(AmqpChannelIdProvider::class);

        $conn = new AmqpConnection($connMock, $idProviderMock, [
            'some-target' => [
                'exchange' => 'some-exchange',
                'routing_key' => 'some-routing-key'
            ]
        ], []);

        $conn->target('some-target-invalid');
    }
}