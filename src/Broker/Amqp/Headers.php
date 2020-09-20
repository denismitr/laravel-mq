<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker\Amqp;


class Headers
{
    public const ATTEMPTS = 'attempts';
    public const LARAVEL_MQ = 'laravel_mq';
}