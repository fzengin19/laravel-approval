<?php

namespace LaravelApproval\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use LaravelApproval\LaravelApprovalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Models\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
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
        config()->set('auth.providers.users.model', User::class);
        config()->set('approvals.user_model', User::class);

        // Run package migrations
        foreach (File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
        // Run tests migrations
        foreach (File::allFiles(__DIR__.'/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
