<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Connectors;


use Denismitr\LaravelMQ\Broker\Connection;
use Denismitr\LaravelMQ\Exception\ConnectionException;

interface Connector
{
    /**
     * @param array $config
     * @throws ConnectionException
     * @return Connection
     */
    public function connect(array $config): Connection;
}