<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


interface Resolver
{
    public function resolve(): void;
}