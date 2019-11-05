<?php

namespace Theomessin\Tus\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load default Laravel migrations (eg. users).
        $this->loadLaravelMigrations();

        // Use our own factories for models.
        $this->withFactories(__DIR__.'/../database/factories');
    }

    protected function getPackageProviders($app)
    {
        return ['Theomessin\Tus\ServiceProvider'];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use our custom storage path for testing.
        $app->useStoragePath(realpath(__DIR__ . '/../storage/'));
    }
}
