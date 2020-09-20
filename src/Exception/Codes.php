<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Exception;


class Codes
{
    const CONNECTION = 1;
    const OPTION_NOT_FOUND = 2;
    const TARGET_INVALID = 3;
    const SOURCE_INVALID = 4;
    const TARGET_NOT_FOUND = 5;
    const SOURCE_NOT_FOUND = 6;
    const CONSUMER_FAILED = 7;
    const CONNECTION_NOT_FOUND = 8;
    const CONNECTIONS_NOT_FOUND = 9;
    const WRONG_DRIVER = 10;
    const NO_DRIVER_CFG = 11;
}