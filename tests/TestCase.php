<?php

namespace LaravelApproval\LaravelApproval\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelApproval\LaravelApproval\LaravelApprovalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LaravelApproval\\LaravelApproval\\Database\\Factories\\'.class_basename($modelName).'Factory'
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
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Run package migrations
        $migration = include __DIR__.'/../database/migrations/create_approval_table.php.stub';
        $migration->up();

        // Run test migrations
        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/Database/Migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
