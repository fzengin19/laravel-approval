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

        // Run package migration
        $migration = include __DIR__.'/../database/migrations/create_approval_table.php.stub';
        $migration->up();

        // Run test migrations manually in correct order for better compatibility
        $testMigrations = [
            __DIR__.'/Database/Migrations/create_posts_table.php',
            __DIR__.'/Database/Migrations/create_users_table.php',
        ];

        foreach ($testMigrations as $migrationPath) {
            if (file_exists($migrationPath)) {
                $migration = include $migrationPath;
                if (is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();
                }
            }
        }
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
    }
}