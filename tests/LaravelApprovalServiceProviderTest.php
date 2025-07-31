<?php

namespace LaravelApproval\Tests;

use LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\Contracts\ApprovalValidatorInterface;
use LaravelApproval\Contracts\StatisticsServiceInterface;
use LaravelApproval\Core\ApprovalEventDispatcher;
use LaravelApproval\Core\ApprovalRepository;
use LaravelApproval\Core\ApprovalValidator;
use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\LaravelApprovalServiceProvider;
use LaravelApproval\Services\ApprovalService;
use LaravelApproval\Services\StatisticsService;
use Mockery\MockInterface;

class LaravelApprovalServiceProviderTest extends TestCase
{
    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_registers_event_listeners_correctly()
    {
        $dispatcher = $this->app->get('events');

        $this->assertTrue($dispatcher->hasListeners(\LaravelApproval\Events\ModelApproved::class));
        $this->assertTrue($dispatcher->hasListeners(\LaravelApproval\Events\ModelRejected::class));
        $this->assertTrue($dispatcher->hasListeners(\LaravelApproval\Events\ModelPending::class));
        $this->assertTrue($dispatcher->hasListeners(\LaravelApproval\Events\ModelApproving::class));
        $this->assertTrue($dispatcher->hasListeners(\LaravelApproval\Events\ModelRejecting::class));
        $this->assertTrue($dispatcher->hasListeners(\LaravelApproval\Events\ModelSettingPending::class));
    }
}
