<?php

namespace LaravelApproval;

use LaravelApproval\Commands\ApprovalStatusCommand;
use LaravelApproval\Services\ApprovalService;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Listeners\SendApprovalNotifications;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApprovalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-approval')
            ->hasConfigFile()
            ->hasMigration('create_approvals_table')
            ->hasCommand(ApprovalStatusCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('laravel-approval', function ($app) {
            return new ApprovalService;
        });
    }

    public function packageBooted(): void
    {
        // Event listener'ları kaydet
        if (config('approvals.features.notifications.enabled', false)) {
            $this->app['events']->listen(
                ModelApproved::class,
                [SendApprovalNotifications::class, 'handle']
            );

            $this->app['events']->listen(
                ModelRejected::class,
                [SendApprovalNotifications::class, 'handle']
            );

            $this->app['events']->listen(
                ModelPending::class,
                [SendApprovalNotifications::class, 'handle']
            );
        }
    }
}
