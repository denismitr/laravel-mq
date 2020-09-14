<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


class AmqpChannelIdProvider
{
    public function provide(): int
    {
        return mt_rand(1, PHP_INT_MAX); // fixme
    }
}