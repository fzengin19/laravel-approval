<?php

namespace LaravelApproval;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use LaravelApproval\Commands\LaravelApprovalCommand;
use LaravelApproval\Commands\ClearApprovalCacheCommand;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Listeners\SendApprovalNotification;
use Illuminate\Support\Facades\Event;

class LaravelApprovalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-approval')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_approvals_table')
            ->hasCommand(LaravelApprovalCommand::class)
            ->hasCommand(ClearApprovalCacheCommand::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        // Register the main approval service
        $this->app->singleton('laravel-approval', function ($app) {
            return new LaravelApproval();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Register event listeners
        $this->registerEventListeners();

        // Publish additional resources
        $this->publishes([
            __DIR__ . '/../config/approval.php' => config_path('approval.php'),
        ], 'laravel-approval-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'laravel-approval-migrations');
    }

    /**
     * Register event listeners for approval events.
     */
    protected function registerEventListeners(): void
    {
        if (config('approval.notifications.enabled', true)) {
            Event::listen(ModelApproved::class, SendApprovalNotification::class);
            Event::listen(ModelRejected::class, SendApprovalNotification::class);
            Event::listen(ModelPending::class, SendApprovalNotification::class);
        }
    }
}
