<?php

namespace Theomessin\Tus;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Theomessin\Tus\Models\Upload;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishMigrations();
        $this->mapTusRoutes();
        $this->bootConsole();
        $this->bindTusModel();
    }

    /**
     * Publish the migrations for this package.
     */
    protected function publishMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Define the routes for the package.
     */
    protected function mapTusRoutes()
    {
        $options = [
            'middleware' => ['web'],
            'prefix' => config('tus.endpoint'),
        ];

        Route::group($options, static function ($router) {
            (new RouteRegistrar($router))->all();
        });
    }

    /**
     * Console specific booting.
     */
    protected function bootConsole()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->registerCommands();
        }
    }

    /**
     * Publish the package config.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('tus.php'),
        ], 'config');
    }

    /**
     * Register package Artisan commands.
     */
    protected function registerCommands()
    {
        $this->commands([]);
    }

    protected function bindTusModel()
    {
        Route::bind('upload', function ($value) {
            $upload = Upload::where('key', $value)->firstOrFail();
            if ($upload->trashed()) abort(404);
            return $upload;
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->applyConfig();
    }

    /**
     * Automatically apply the package config
     */
    protected function applyConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'tus');
    }
}
