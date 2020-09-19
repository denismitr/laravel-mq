<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpProducer;
use Denismitr\LaravelMQ\Broker\Message;
use Denismitr\LaravelMQ\Tests\BaseTestCase;
use Mockery as m;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpProducerTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_produce_a_json_message()
    {
        $msg = Message::fromJsonData([
            'foo' => 'bar',
            'baz' => 123,
        ]);

        $channelIdProvider = m::mock(AmqpChannelIdProvider::class);
        $connection = m::mock(AbstractConnection::class);
        $channel = m::mock(AMQPChannel::class);

        $producer = new AmqpProducer(
            $connection,
            $channelIdProvider,
            'some-exchange',
            'some-routing-key'
        );

        $channelIdProvider->expects('provide')->andReturn(10);
        $connection->expects('channel')->with(10)->andReturn($channel);

        $channel->expects('basic_publish')->withArgs(function(AMQPMessage $message, string $exchange, string $routingKey) {
            $this->assertEquals('{"foo":"bar","baz":123}', $message->body, 'message body invalid');
            $this->assertEquals([
                'content_type' => 'application/json',
                'delivery_mode' => 2,
            ], $message->get_properties(), 'message properties invalid');
            $this->assertEquals('some-exchange', $exchange, 'exchange invalid');
            $this->assertEquals('some-routing-key', $routingKey, 'routing key invalid');

            return true;
        })->andReturn(null);

        $channel->expects('is_open')->andReturn(false);

        $producer->produce($msg);
    }
}