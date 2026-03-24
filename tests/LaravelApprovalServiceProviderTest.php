<?php

namespace LaravelApproval\Tests;

use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Contracts\StatisticsServiceInterface;
use LaravelApproval\Core\ApprovalEventDispatcher;
use LaravelApproval\Core\ApprovalRepository;
use LaravelApproval\Core\ApprovalValidator;
use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelApproving;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelRejecting;
use LaravelApproval\Events\ModelSettingPending;
use LaravelApproval\Services\ApprovalService;
use LaravelApproval\Services\StatisticsService;
use PHPUnit\Framework\Attributes\Test;

class LaravelApprovalServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_main_approval_service_as_a_singleton()
    {
        $this->assertInstanceOf(
            ApprovalService::class,
            $this->app->get('laravel-approval')
        );

        $this->assertSame(
            $this->app->get('laravel-approval'),
            $this->app->get('laravel-approval')
        );
    }

    #[Test]
    public function it_binds_interfaces_to_their_concrete_implementations()
    {
        $this->assertInstanceOf(
            ApprovalRepository::class,
            $this->app->get(ApprovalRepositoryInterface::class)
        );

        $this->assertInstanceOf(
            ApprovalValidator::class,
            $this->app->get(ApprovalValidatorInterface::class)
        );

        $this->assertInstanceOf(
            StatisticsService::class,
            $this->app->get(StatisticsServiceInterface::class)
        );
    }

    #[Test]
    public function it_registers_core_components_as_singletons()
    {
        $this->assertSame(
            $this->app->get(ApprovalEventDispatcher::class),
            $this->app->get(ApprovalEventDispatcher::class)
        );

        $this->assertSame(
            $this->app->get(WebhookDispatcher::class),
            $this->app->get(WebhookDispatcher::class)
        );
    }

    #[Test]
    public function it_registers_event_listeners_correctly()
    {
        $dispatcher = $this->app->get('events');

        $this->assertTrue($dispatcher->hasListeners(ModelApproved::class));
        $this->assertTrue($dispatcher->hasListeners(ModelRejected::class));
        $this->assertTrue($dispatcher->hasListeners(ModelPending::class));
        $this->assertTrue($dispatcher->hasListeners(ModelApproving::class));
        $this->assertTrue($dispatcher->hasListeners(ModelRejecting::class));
        $this->assertTrue($dispatcher->hasListeners(ModelSettingPending::class));
    }
}
