<?php

namespace LaravelApproval\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelApproval\LaravelApprovalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'VendorName\\Skeleton\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelApprovalServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Run package migrations
        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }

        // Run workbench migrations
        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../workbench/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
