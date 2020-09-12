<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpResolve
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

    public function __invoke()
    {
        $this->channel->basic_ack($this->message->getDeliveryTag());
    }
}