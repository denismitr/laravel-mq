<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


interface Control
{
    public function reject(bool $requeue): void;
    public function resolve(): void;
}