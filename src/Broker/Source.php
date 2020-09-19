<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


use Closure;
use Denismitr\LaravelMQ\Exception\ConsumerException;
use Denismitr\LaravelMQ\Exception\ConsumerTimeoutException;

interface Source
{
    /**
     * @param Closure $closure
     * @param array $options
     * @throws ConsumerException
     * @throws ConsumerTimeoutException
     */
    public function read(Closure $closure, array $options = []): void;
}