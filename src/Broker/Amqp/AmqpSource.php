<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


use Closure;
use Denismitr\LaravelMQ\Broker\Message;
use Denismitr\LaravelMQ\Broker\Source;
use Denismitr\LaravelMQ\Exception\ConsumerException;
use Denismitr\LaravelMQ\Exception\ConsumerTimeoutException;
use Denismitr\LaravelMQ\Exception\StopConsuming;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class AmqpSource implements Source
{
    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var AMQPChannel|null
     */
    private $channel = null;

    /**
     * @var AmqpChannelIdProvider
     */
    private $channelIdProvider;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var int
     */
    private $timeout;

    /**
     * AmqpConsumer constructor.
     * @param AbstractConnection $connection
     * @param AmqpChannelIdProvider $channelIdProvider
     * @param string $tag
     * @param string $queue
     * @param int $timeout
     */
    public function __construct(
        AbstractConnection $connection,
        AmqpChannelIdProvider $channelIdProvider,
        string $tag,
        string $queue,
        int $timeout
    )
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->channelIdProvider = $channelIdProvider;
        $this->tag = $tag;
        $this->timeout = $timeout;
    }

    public function __destruct()
    {
        if ($this->channel && $this->channel->is_open()) {
            $this->channel->close();
        }
    }

    /**
     * @param Closure $closure
     * @param array $options
     * @throws ConsumerException
     * @throws ConsumerTimeoutException
     */
    public function read(Closure $closure, array $options = []): void
    {
        $this->getChannel()->basic_consume(
            $this->queue,
            $this->tag,
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($closure) {
                $control = new AmqpControl($this->channel, $message);
                $body = $message->getBody();
                $encoding = $message->getContentEncoding();

                $attempts = null;
                if ($message->has(Headers::LARAVEL_MQ)) {
                    $laravelMq = $message->get(Headers::LARAVEL_MQ);
                    if (isset($laravelMq[Headers::ATTEMPTS])) {
                        $attempts = (int) $laravelMq[Headers::ATTEMPTS];
                    }
                }

                if ($closure(Message::fromRawBody($body, $encoding, $attempts), $control) === false) {
                    throw new StopConsuming();
                }
            }
        );

        while ($this->getChannel()->is_consuming()) {
            try {
                $this->getChannel()->wait(null, false, $this->timeout);
            } catch (StopConsuming $e) {
                return;
            } catch (AMQPTimeoutException $e) {
                throw new ConsumerTimeoutException($this->timeout, $this->tag);
            } catch (Throwable $t) {
                throw ConsumerException::from($this->queue, $this->tag, $t);
            }
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
}