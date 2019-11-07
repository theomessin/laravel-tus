<?php

namespace Theomessin\Tus\Tests;

use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /** @var User */
    protected $user;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Publish migration.
        $this->artisan('vendor:publish', [
            '--provider' => \Theomessin\Tus\ServiceProvider::class,
        ])->run();

        // Migrate the in memory database.
        $this->artisan('migrate:fresh')->run();

        // Load default Laravel migrations (eg. users).
        $this->loadLaravelMigrations();

        // Use our own factories for models.
        $this->withFactories(__DIR__.'/../database/factories');

        // Create a default user for all requests.
        $this->user = factory(User::class)->create();

        // Register all requests acting as this user.
        $this->actingAs($this->user);
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
