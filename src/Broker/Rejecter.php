<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


interface Rejecter
{
    public function reject(bool $requeue): void;
}