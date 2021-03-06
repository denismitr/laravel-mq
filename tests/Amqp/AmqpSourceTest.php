<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Closure;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpSource;
use Denismitr\LaravelMQ\Broker\Control;
use Denismitr\LaravelMQ\Broker\Message;
use Denismitr\LaravelMQ\Tests\BaseTestCase;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use Mockery as m;

class AmqpSourceTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_read_a_message_from_queue()
    {
        $idProviderMock = m::mock(AmqpChannelIdProvider::class);
        $connMock = m::mock(AbstractConnection::class);
        $channelMock = m::mock(AMQPChannel::class);

        $tag = 'some-tag';
        $queue = 'some-queue';
        $timeout = 5;

        $source = new AmqpSource(
            $connMock,
            $idProviderMock,
            $tag,
            $queue,
            $timeout
        );

        $idProviderMock->expects('provide')->once()->andReturn(99);
        $connMock->expects('channel')->once()->with(99)->andReturn($channelMock);

        $channelMock->expects('basic_consume')
            ->once()
            ->withArgs(function(
                string $queue,
                string $tag,
                bool $noLocal,
                bool $noAck,
                bool $exclusive,
                bool $noWait,
                Closure $closure
            ) {
                return true;
            })
        ;

        $channelMock->expects('is_consuming')->andReturn(true);
        $channelMock->expects('is_consuming')->andReturn(false);
        $channelMock->expects('wait')->once();

        $channelMock->expects('is_open')->times(3)->andReturn(true);
        $channelMock->expects('close')->times(0);

        $source->read(function(Message $message, Control $control) {
            $this->assertFalse($message->isEmpty());
            $this->assertTrue($message->isJson());

            $control->resolve();

            return false;
        });
    }
}