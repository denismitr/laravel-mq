<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


interface Target
{
    public function send(Message $message, array $options = []): void;
}