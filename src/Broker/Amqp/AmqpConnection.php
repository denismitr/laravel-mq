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

class AmqpConnection implements Connection
{
    /** @var AbstractConnection */
    private $connection;

    /** @var AmqpConsumer */
    private $consumer;

    /** @var AmqpProducer|null */
    private $producer = null;

    /** @var array */
    private $targets;

    /** @var array */
    private $sources;

    /** @var AmqpChannelIdProvider */
    private $idProvider;

    /**
     * AmqpExchange constructor.
     * @param AbstractConnection $connection
     * @param AmqpChannelIdProvider $idProvider
     * @param array $targets
     * @param array $sources
     */
    public function __construct(
        AbstractConnection $connection,
        AmqpChannelIdProvider $idProvider,
        array $targets,
        array $sources
    )
    {
        $this->connection = $connection;
        $this->targets = $targets;
        $this->sources = $sources;
        $this->idProvider = $idProvider;
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

        if ( ! $this->producer) {
            $this->producer = new AmqpProducer(
                $this->connection,
                $this->idProvider,
                $exchange,
                $routingKey
            );
        }

        $this->producer->produce($message);
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
        $timeout = (int) Arr::get($params, 'timeout', 10);

        if ( ! $consumerTag || ! $queueName) {
            throw ConfigurationException::targetInvalid(
                $source,
                "Source must contain consumer tag and queue name"
            );
        }

        if ( ! $this->consumer) {
            $this->consumer = new AmqpConsumer(
                $this->connection,
                $this->idProvider,
                $consumerTag,
                $queueName,
                $timeout
            );
        }

        $this->consumer->consume($closure);
    }
}