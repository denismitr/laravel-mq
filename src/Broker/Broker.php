<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


use Denismitr\LaravelMQ\Exception\ConfigurationException;
use Denismitr\LaravelMQ\Exception\ConnectionException;

interface Broker
{
    /**
     * @param string $connection
     * @return Connection
     * @throws ConfigurationException
     * @throws ConnectionException
     */
    public function connection(string $connection): Connection;
}