<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests;


use Denismitr\LaravelMQ\LaravelMQServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelMQServiceProvider::class,
        ];
    }
}