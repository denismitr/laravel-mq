<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Connection;


use Denismitr\LaravelMQ\Exception\ConnectionException;
use PhpAmqpLib\Connection\AbstractConnection;

interface Connector
{
    /**
     * @param array $config
     * @return AbstractConnection
     * @throws ConnectionException
     */
    public function connect(array $config): AbstractConnection;
}