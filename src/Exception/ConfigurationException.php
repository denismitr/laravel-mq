<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Exception;


use League\CommonMark\Inline\Element\Code;

class ConfigurationException extends LaravelMQException
{
    public static function optionNotFound(string $path, string $expected = null): ConfigurationException
    {
        $msg = "Configuration with path mq.{$path} not found.";

        if ($expected) {
            $msg .= " Expected {$expected}";
        }

        return new static($msg, Codes::OPTION_NOT_FOUND);
    }

    public static function connectionsNotFound(): ConfigurationException
    {
        return new static("Connections key in [mq.php] config file not found!", Codes::CONNECTIONS_NOT_FOUND);
    }

    public static function connectionNotFound(string $connection): ConfigurationException
    {
        return new static("Connection [{$connection}] not found!", Codes::CONNECTION_NOT_FOUND);
    }

    public static function targetNotFound(string $target): ConfigurationException
    {
        return new static("Target {$target} not found", Codes::TARGET_NOT_FOUND);
    }

    public static function sourceNotFound(string $source): ConfigurationException
    {
        return new static("Source {$source} not found", Codes::SOURCE_NOT_FOUND);
    }

    public static function sourceInvalid(string $source, string $details = ''): ConfigurationException
    {
        return new static("Source {$source} is not configured properly. {$details}", Codes::SOURCE_INVALID);
    }

    public static function targetInvalid(string $target, string $details = ''): ConfigurationException
    {
        return new static("Target {$target} is not configured properly. {$details}", Codes::TARGET_INVALID);
    }

    public static function invalidDriver(string $driver)
    {
        return new static("Driver [{$driver}] is not supported.", Codes::WRONG_DRIVER);
    }

    public static function driverNotFound()
    {
        return new static("Driver configuration not found.", Codes::NO_DRIVER_CFG);
    }

    public static function invalidConnectionClass(string $className, string $msg)
    {
        return new static("Invalid connection class {$className}: {$msg}", Codes::INVALID_CONNECTION_CLASS);
    }
}