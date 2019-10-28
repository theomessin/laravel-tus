<?php

namespace Theomessin\Tus\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return ['Theomessin\Tus\ServiceProvider'];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useStoragePath(realpath(__DIR__ . '/../storage/'));
    }
}
