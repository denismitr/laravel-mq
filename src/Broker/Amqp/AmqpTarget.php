<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Denismitr\LaravelMQ\Broker\Message;
use Denismitr\LaravelMQ\Broker\Target;
use Denismitr\LaravelMQ\Exception\ProducerException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class AmqpTarget implements Target
{
    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var AMQPChannel|null
     */
    private $channel = null;

    /**
     * @var AmqpChannelIdProvider
     */
    private $channelIdProvider;

    /**
     * AmqpProducer constructor.
     * @param AbstractConnection $connection
     * @param AmqpChannelIdProvider $channelIdProvider
     * @param string $exchange
     * @param string $routingKey
     */
    public function __construct(
        AbstractConnection $connection,
        AmqpChannelIdProvider $channelIdProvider,
        string $exchange,
        string $routingKey = ''
    )
    {
        $this->connection = $connection;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->channelIdProvider = $channelIdProvider;
    }

    public function __destruct()
    {
        if ($this->channel && $this->channel->is_open()) {
            $this->channel->close();
        }
    }

    /**
     * @param Message $message
     * @param array $options
     * @throws ProducerException
     */
    public function send(Message $message, array $options = []): void
    {
        try {
            $this->getChannel()->basic_publish(
                $this->createMessage($message, $options),
                $this->exchange,
                $this->routingKey
            );
        } catch (Throwable $t) {
            throw ProducerException::from($t);
        }
    }

    private function getChannel(): AMQPChannel
    {
        if ( ! $this->channel || ! $this->channel->is_open()) {
            $this->channel = $this->connection->channel(
                $this->channelIdProvider->provide()
            );
        }

        return $this->channel;
    }

    private function createMessage(Message $message, array $options = []): AMQPMessage
    {
        $properties = [
            'content_type' => $message->contentEncoding(),
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        $payload = $message->isJson() ? json_encode($message) : $message->rawBody();

        $msg = new AMQPMessage($payload, $properties);

        if ($message->hasAttempt()) {
            $msg->set('application_headers', new AMQPMessage([
                Headers::LARAVEL_MQ => [
                    Headers::ATTEMPTS => $message->attempt(),
                ]
            ]));
        }

        return $msg;
    }
}