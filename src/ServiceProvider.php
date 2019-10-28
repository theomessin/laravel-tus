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
        $this->mapTusRoutes();
        $this->bootConsole();
        $this->bindTusModel();
    }

    /**
     * Define the routes for the package.
     */
    protected function mapTusRoutes()
    {
        $options = [
            'prefix' => 'tus',
            'middleware' => ['web'],
            'namespace' => '\Theomessin\Tus\Http\Controllers',
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
            return Upload::find($value) ?? abort(404);
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
