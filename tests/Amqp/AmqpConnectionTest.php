<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests\Amqp;


use Denismitr\LaravelMQ\Broker\Amqp\AmqpChannelIdProvider;
use Denismitr\LaravelMQ\Broker\Amqp\AmqpConnection;
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
    public function it_can_instantiate_producer_and_send_message()
    {
        $msg = Message::fromJsonData([
            'foo' => 'bar',
            'baz' => 123,
        ]);

        $connMock = m::mock(AbstractConnection::class);
        $idProviderMock = m::mock(AmqpChannelIdProvider::class);
        $channelMock = m::mock(AMQPChannel::class);

        $conn = new AmqpConnection($connMock, $idProviderMock, [
            'some-target' => [
                'exchange' => 'some-exchange',
                'routing_key' => 'some-routing-key'
            ]
        ], []);

        $idProviderMock->expects('provide')->andReturn(99);

        $connMock->expects('channel')->with(99)->andReturn($channelMock);

        $channelMock->expects('basic_publish')->withArgs(function(AMQPMessage $message, string $exchange, string $routingKey) {
            $this->assertEquals('{"foo":"bar","baz":123}', $message->body, 'message body invalid');
            $this->assertEquals([
                'content_type' => 'application/json',
                'delivery_mode' => 2,
            ], $message->get_properties(), 'message properties invalid');
            $this->assertEquals('some-exchange', $exchange, 'exchange invalid');
            $this->assertEquals('some-routing-key', $routingKey, 'routing key invalid');
            return true;
        })->andReturn(null);

        $channelMock->expects('is_open')->once()->andReturn(true);
        $channelMock->expects('close')->once();

        $conn->produce('some-target', $msg);
    }

    /**
     * @test
     */
    public function it_will_throw_exception_on_invalid_target()
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

        $conn->produce('some-target-invalid', Message::fromJsonData(['foo' => 555]));
    }
}