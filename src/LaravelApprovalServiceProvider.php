<?php

namespace LaravelApproval;

use LaravelApproval\Commands\ApprovalStatusCommand;
use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Contracts\StatisticsServiceInterface;
use LaravelApproval\Core\ApprovalRepository;
use LaravelApproval\Core\ApprovalValidator;
use LaravelApproval\Services\StatisticsService;
use LaravelApproval\Core\ApprovalEventDispatcher;
use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelApproving;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelRejecting;
use LaravelApproval\Events\ModelSettingPending;
use LaravelApproval\Listeners\HandleModelApproved;
use LaravelApproval\Listeners\HandleModelRejected;
use LaravelApproval\Listeners\HandleModelPending;
use LaravelApproval\Listeners\HandleModelApproving;
use LaravelApproval\Listeners\HandleModelRejecting;
use LaravelApproval\Listeners\HandleModelSettingPending;
use LaravelApproval\Services\ApprovalService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApprovalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-approval')
            ->hasConfigFile('approvals')
            ->hasMigration('create_approvals_table')
            ->hasCommand(ApprovalStatusCommand::class);
    }

    public function packageRegistered(): void
    {
        // Bind repository and validator
        $this->app->bind(ApprovalRepositoryInterface::class, ApprovalRepository::class);
        $this->app->bind(ApprovalValidatorInterface::class, ApprovalValidator::class);
        $this->app->bind(StatisticsServiceInterface::class, StatisticsService::class);

        // Bind event dispatcher
        $this->app->singleton(ApprovalEventDispatcher::class);
        $this->app->singleton(WebhookDispatcher::class);

        // Bind main service
        $this->app->singleton('laravel-approval', function ($app) {
            return new ApprovalService($app->make(StatisticsServiceInterface::class));
        });
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        // Register event listeners
        $this->app['events']->listen(
            ModelApproved::class,
            HandleModelApproved::class
        );

        $this->app['events']->listen(
            ModelRejected::class,
            HandleModelRejected::class
        );

        $this->app['events']->listen(
            ModelPending::class,
            HandleModelPending::class
        );

        $this->app['events']->listen(
            ModelApproving::class,
            HandleModelApproving::class
        );

        $this->app['events']->listen(
            ModelRejecting::class,
            HandleModelRejecting::class
        );

        $this->app['events']->listen(
            ModelSettingPending::class,
            HandleModelSettingPending::class
        );
    }
}
