<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Broker\Source;
use Denismitr\LaravelMQ\Broker\Target;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AbstractConnection;

class AmqpConnection implements Connection
{
    /** @var AbstractConnection */
    private $connection;

    /** @var Source[]] */
    private $sourceInstances = [];

    /** @var Target[] */
    private $targetInstances = [];

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
     * @return Target
     * @throws ConfigurationException
     */
    public function target(string $target): Target
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

        if ( ! isset($this->targetInstances[$target])) {
            $this->targetInstances[$target] = new AmqpTarget(
                $this->connection,
                $this->idProvider,
                $exchange,
                $routingKey
            );
        }

        return $this->targetInstances[$target];
    }

    /**
     * @param string $source
     * @return Source
     * @throws ConfigurationException
     */
    public function source(string $source): Source
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

        if ( ! isset($this->sourceInstances[$source])) {
            $this->sourceInstances[$source] = new AmqpSource(
                $this->connection,
                $this->idProvider,
                $consumerTag,
                $queueName,
                $timeout
            );
        }

        return $this->sourceInstances[$source];
    }
}