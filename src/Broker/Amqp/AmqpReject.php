<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Denismitr\LaravelMQ\Broker\Rejecter;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpReject implements Rejecter
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

    public function reject(bool $requeue): void
    {
        $this->channel->basic_reject($this->message->getDeliveryTag(), $requeue);
    }
}