<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Exception;


class ProducerException extends LaravelMQException
{
    public static function from(\Throwable $t): ProducerException
    {
        return new static("Producer exception", 0, $t); // fixme
    }
}