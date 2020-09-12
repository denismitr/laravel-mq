<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Exception;


class ConnectionException extends LaravelMQException
{
    public static function from(\Throwable $t): ConnectionException
    {
        return new static("Laravel MQ connection failed", Codes::CONNECTION, $t);
    }
}