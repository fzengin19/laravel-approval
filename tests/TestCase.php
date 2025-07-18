<?php

namespace LaravelApproval\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use LaravelApproval\LaravelApprovalServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\Company;
use LaravelApproval\Tests\Models\Post;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected static $migrationsRun = false;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');

        // Run migrations only once for all tests
        if (!static::$migrationsRun) {
            $this->runMigrations();
            static::$migrationsRun = true;
        }

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LaravelApproval\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Set up test configuration
        $this->setUpTestConfiguration();
    }

    protected function runMigrations()
    {
        // Publish and run migrations
        $this->artisan('vendor:publish', [
            '--provider' => LaravelApprovalServiceProvider::class,
            '--tag' => 'laravel-approval-migrations'
        ]);

        $this->artisan('migrate');
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelApprovalServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Use in-memory SQLite for faster tests
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => dirname(__DIR__, 1) . '/database/testing.sqlite',
            'prefix' => '',
        ]);

        // Set auth model
        config()->set('auth.providers.users.model', User::class);

        // Disable unnecessary services for testing
        config()->set('cache.default', 'database');
        config()->set('queue.default', 'sync');
        config()->set('session.driver', 'array');
        config()->set('mail.default', 'array');
        config()->set('broadcasting.default', 'log');

        // Disable Ray for testing to prevent memory issues
        config()->set('ray.enabled', false);

        // Create users table for testing
        $this->createUsersTable($app);

        // Create test models tables
        $this->createTestModelTables($app);
    }

    protected function setUpTestConfiguration()
    {
        config()->set('approval.models', [
            'default' => [
                'column' => 'approved_at',
                'fallback_to_pivot' => true,
                'auto_scope' => false, // Disable for testing
                'events' => true,
                'show_only_approved_by_default' => false,
                'cache' => false, // Disable cache for faster tests
                'rate_limiting' => false, // Disable rate limiting for faster tests
            ],
            Job::class => [
                'column' => 'approved_at',
                'fallback_to_pivot' => true,
                'auto_scope' => false,
                'events' => true,
                'show_only_approved_by_default' => false,
                'cache' => false,
                'rate_limiting' => false,
            ],
            Company::class => [
                'column' => 'is_approved',
                'fallback_to_pivot' => true,
                'auto_scope' => false,
                'events' => true,
                'show_only_approved_by_default' => false,
                'cache' => false,
                'rate_limiting' => false,
            ],
            Post::class => [
                'column' => 'approval_status',
                'fallback_to_pivot' => true,
                'auto_scope' => false,
                'events' => true,
                'show_only_approved_by_default' => false,
                'cache' => false,
                'rate_limiting' => false,
            ],
            Article::class => [
                'column' => null,
                'fallback_to_pivot' => true,
                'auto_scope' => false,
                'events' => true,
                'show_only_approved_by_default' => false,
                'cache' => false,
                'rate_limiting' => false,
            ],
        ]);

        // Disable rejection reason validation for testing
        config()->set('approval.rejection_reasons', []);
    }

    protected function createUsersTable($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        if (!$schema->hasTable('users')) {
            $schema->create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamps();
            });
        }
    }

    protected function createTestModelTables($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        // Create Job model table with approved_at column
        if (!$schema->hasTable('jobs')) {
            $schema->create('jobs', function ($table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        // Create Company model table with is_approved column
        if (!$schema->hasTable('companies')) {
            $schema->create('companies', function ($table) {
                $table->id();
                $table->string('name');
                $table->text('description');
                $table->boolean('is_approved')->nullable();
                $table->timestamps();
            });
        }

        // Create Post model table with approval_status column
        if (!$schema->hasTable('posts')) {
            $schema->create('posts', function ($table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();
            });
        }

        // Create Article model table without approval columns
        if (!$schema->hasTable('articles')) {
            $schema->create('articles', function ($table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->timestamps();
            });
        }
    }
}
