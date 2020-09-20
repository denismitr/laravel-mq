<?php

declare(strict_types=1);


namespace Denismitr\LaravelMQ;


use Illuminate\Support\ServiceProvider;

class LaravelMQServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mq.php', 'mq');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mq.php' => config_path('mq.php'),
        ]);
    }
}