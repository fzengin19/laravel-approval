<?php

namespace LaravelApproval;

use LaravelApproval\Commands\ApprovalStatusCommand;
use LaravelApproval\Services\ApprovalService;
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
}
