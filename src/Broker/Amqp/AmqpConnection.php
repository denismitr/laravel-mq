<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Closure;
use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Broker\Message;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConsumerException;
use Denismitr\LaravelMQ\Exception\ProducerException;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class AmqpConnection implements Connection
{
    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $publishChannel;

    /**
     * @var AMQPChannel
     */
    private $consumeChannel;

    /**
     * @var array
     */
    private $targets;
    /**
     * @var array
     */
    private $sources;

    /**
     * AmqpExchange constructor.
     * @param AbstractConnection $connection
     * @param array $targets
     * @param array $sources
     */
    public function __construct(AbstractConnection $connection, array $targets, array $sources)
    {
        $this->connection = $connection;
        $this->targets = $targets;
        $this->sources = $sources;
    }

    public function __destruct()
    {
        if ($this->publishChannel) {
            $this->publishChannel->close();
        }
    }

    /**
     * @param string $target
     * @param Message $message
     * @throws ConfigurationException|ProducerException
     */
    public function produce(string $target, Message $message): void
    {
        $params = $this->targets[$target] ?? null;
        if ( ! $params) {
            throw ConfigurationException::targetNotFound($target);
        }

        $exchange = Arr::get($params, 'exchange', null);
        $routingKey = Arr::get($params, 'routing_key', null);

        if ( ! $exchange || ! $routingKey) {
            throw ConfigurationException::targetInvalid(
                $target,
                "Target must contain exchange name and routing key"
            );
        }

        try {
            $this->getPublishChannel()->basic_publish($this->createMessage($message), $exchange, $routingKey);
        } catch (Throwable $t) {
            throw ProducerException::from($t);
        }
    }

    /**
     * @param string $source
     * @param Closure $closure
     * @throws ConfigurationException
     * @throws ConsumerException
     */
    public function consume(string $source, Closure $closure): void
    {
        $params = $this->sources[$source] ?? null;
        if ( ! $params) {
            throw ConfigurationException::sourceNotFound($source);
        }

        $consumerTag = Arr::get($params, 'tag', null);
        $queueName = Arr::get($params, 'name', null);
        $timeout = Arr::get($params, 'timeout', 10);

        if ( ! $consumerTag || ! $queueName) {
            throw ConfigurationException::targetInvalid(
                $source,
                "Source must contain consumer tag and queue name"
            );
        }

        $this->getConsumeChannel()->basic_consume(
            $queueName,
            $consumerTag,
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($closure) {
                $resolve = new AmqpResolve($this->consumeChannel, $message);
                $reject = new AmqpReject($this->consumeChannel, $message);
                $body = $message->getBody();
                $encoding = $message->getContentEncoding();
                $closure(Message::fromRawBody($body, $encoding), $resolve, $reject);
            }
        );

        while ($this->getConsumeChannel()->is_consuming()) {
            try {
                $this->getConsumeChannel()->wait(null, false, (int) $timeout);
            } catch (AMQPTimeoutException $e) {
                return;
            } catch (Throwable $t) {
                throw ConsumerException::from($queueName, $consumerTag, $t);
            }
        }
    }

    private function getPublishChannel(): AMQPChannel
    {
        if ( ! $this->publishChannel || $this->publishChannel->is_open()) {
            $id = mt_rand(1, PHP_INT_MAX); // fixme
            $this->publishChannel = $this->connection->channel($id);
        }

        return $this->publishChannel;
    }

    private function getConsumeChannel(): AMQPChannel
    {
        if ( ! $this->consumeChannel) {
            $id = mt_rand(1, PHP_INT_MAX); // fixme
            $this->consumeChannel = $this->connection->channel($id);
        }

        return $this->consumeChannel;
    }

    private function createMessage(Message $message): AMQPMessage
    {
        $properties = [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        $payload = json_encode($message);

        return new AMQPMessage($payload, $properties);
    }
}