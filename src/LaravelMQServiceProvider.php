<?php

declare(strict_types=1);


namespace Denismitr\LaravelMQ;


use Denismitr\LaravelMQ\Broker\Broker;
use Denismitr\LaravelMQ\Broker\DefaultBroker;
use Denismitr\LaravelMQ\Connectors\Factory;
use Illuminate\Support\ServiceProvider;

class LaravelMQServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mq.php', 'mq');

        $this->app->bind(Broker::class, function() {
            $config = config('mq');

            return new DefaultBroker(new Factory(), $config);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mq.php' => config_path('mq.php'),
        ]);
    }
}