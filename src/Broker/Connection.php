<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;

use Closure;
use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConsumerException;
use Denismitr\LaravelMQ\Exception\ProducerException;

interface Connection
{
    /**
     * @param string $target
     * @param Message $message
     * @throws ConfigurationException|ProducerException
     */
    public function produce(string $target, Message $message): void;

    /**
     * @param string $source
     * @param Closure $closure
     * @throws ConfigurationException
     * @throws ConsumerException
     */
    public function consume(string $source, Closure $closure): void;
}