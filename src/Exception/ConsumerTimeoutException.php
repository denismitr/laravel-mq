<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Exception;


class ConsumerTimeoutException extends ConsumerException
{
    /**
     * ConsumerTimeoutException constructor.
     * @param int $timeout
     * @param string $consumerName
     */
    public function __construct(int $timeout, string $consumerName = '')
    {
        $msg = "Timeout after {$timeout} seconds";
        if ($consumerName) {
            $msg = "Consumer [{$consumerName}]: {$msg}";
        }

        parent::__construct($msg);
    }
}