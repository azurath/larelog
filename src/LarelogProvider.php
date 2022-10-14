<?php

namespace Azurath\Larelog;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LarelogProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('larelog.php'),
            ], 'config');
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
        View::addNamespace('larelog', [__DIR__ . '/../views']);
        $this->loadViewsFrom(__DIR__ . '/../views', 'larelog');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'larelog');
    }
}
