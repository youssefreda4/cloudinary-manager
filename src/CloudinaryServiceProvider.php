<?php

namespace CloudinaryManager;

use Illuminate\Support\ServiceProvider;
use CloudinaryManager\CloudinaryService;

class CloudinaryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cloudinary.php', 'cloudinary'
        );

        $this->app->singleton(CloudinaryService::class, function ($app) {
            return new CloudinaryService();
        });

        $this->app->alias(CloudinaryService::class, 'cloudinary');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cloudinary.php' => config_path('cloudinary.php'),
            ], 'cloudinary-config');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}