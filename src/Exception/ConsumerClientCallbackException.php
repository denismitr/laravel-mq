<?php


namespace Denismitr\LaravelMQ\Exception;


class ConsumerClientCallbackException extends ConsumerException
{
    public static function from(string $queue, string $consumerName, \Throwable $t): ConsumerException
    {
        return new static("Consumer {$consumerName} client callback exception when consuming {$queue}");
    }
}