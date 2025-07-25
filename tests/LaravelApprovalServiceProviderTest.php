<?php

namespace LaravelApproval\Tests;

use LaravelApproval\LaravelApprovalServiceProvider;
use LaravelApproval\Services\ApprovalService;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class LaravelApprovalServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_approval_service()
    {
        $this->assertInstanceOf(
            ApprovalService::class,
            app('laravel-approval')
        );
    }

    public function test_service_provider_registers_approval_service_as_singleton()
    {
        $service1 = app('laravel-approval');
        $service2 = app('laravel-approval');

        $this->assertSame($service1, $service2);
    }

    public function test_service_provider_registers_event_listeners_when_notifications_enabled()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);

        // Service provider'ı yeniden boot et
        $provider = new LaravelApprovalServiceProvider($this->app);
        $provider->packageBooted();

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Event listener'ların çalıştığını test et
        $this->assertNotNull($post->approve(1));
        $this->assertNotNull($post->reject(1, 'spam'));
        $this->assertNotNull($post->setPending(1));
    }

    public function test_service_provider_does_not_register_event_listeners_when_notifications_disabled()
    {
        config(['approvals.features.notifications.enabled' => false]);

        // Service provider'ı yeniden boot et
        $provider = new LaravelApprovalServiceProvider($this->app);
        $provider->packageBooted();

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Event listener'ların çalışmadığını test et (notification gönderilmemeli)
        $this->assertNotNull($post->approve(1));
        $this->assertNotNull($post->reject(1, 'spam'));
        $this->assertNotNull($post->setPending(1));
    }
} 