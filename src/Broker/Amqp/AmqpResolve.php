<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Denismitr\LaravelMQ\Broker\Resolver;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpResolve implements Resolver
{
    /**
     * @var AMQPMessage
     */
    private $message;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * AmqpResolve constructor.
     * @param AMQPChannel $channel
     * @param AMQPMessage $message
     */
    public function __construct(AMQPChannel $channel, AMQPMessage $message)
    {
        $this->message = $message;
        $this->channel = $channel;
    }

    public function resolve(): void
    {
        $this->channel->basic_ack($this->message->getDeliveryTag());
    }
}