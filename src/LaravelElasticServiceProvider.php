<?php

namespace Amitav\LaravelElastic;

use Illuminate\Support\ServiceProvider;

class LaravelElasticServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laraelastic.php' => config_path('laraelastic.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laraelastic.php', 'laraelastic');
    }
}
