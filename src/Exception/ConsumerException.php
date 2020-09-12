<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Exception;


class ConsumerException extends \Exception
{
    public static function from(string $queue, string $consumerName, \Throwable $t): ConsumerException
    {
        return new static(
            "Consume process failed for queue {$queue} for consumer with tag {$consumerName}",
            Codes::CONSUMER_FAILED,
            $t
        );
    }
}