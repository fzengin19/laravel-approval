<?php

namespace LaravelApproval\LaravelApproval;

use LaravelApproval\LaravelApproval\Commands\LaravelApprovalCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasMigration('create_approval_table')
            ->hasCommand(LaravelApprovalCommand::class);
    }
}
