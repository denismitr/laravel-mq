<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


use Denismitr\LaravelMQ\Exception\ConfigurationException;

interface Connection
{
    /**
     * @param string $target
     * @return Target
     * @throws ConfigurationException
     */
    public function target(string $target): Target;

    /**
     * @param string $source
     * @return Source
     * @throws ConfigurationException
     */
    public function source(string $source): Source;
}