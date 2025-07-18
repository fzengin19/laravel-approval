<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Commands\ClearApprovalCacheCommand;
use LaravelApproval\Commands\LaravelApprovalCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_clear_all_cache_command()
    {
        // Enable cache for this test
        config()->set('approval.cache.enabled', true);
        config()->set('approval.models.' . Job::class . '.cache', true);
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Cache some approval status
        $job->getApprovalStatus();
        $cacheKey = 'approval_status_' . $job->getTable() . '_' . $job->id;
        

        
        $this->assertTrue(Cache::has($cacheKey));

        // Run clear cache command
        $this->artisan('approval:clear-cache')
            ->expectsOutput('✅ All cache cleared successfully')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_clear_only_discovery_cache()
    {
        // Cache model discovery
        Cache::put('approval_models_discovered', ['test'], 3600);
        $this->assertTrue(Cache::has('approval_models_discovered'));

        // Run clear discovery cache command
        $this->artisan('approval:clear-cache', ['--discovery' => true])
            ->expectsOutput('✅ Model discovery cache cleared')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_show_approval_status_command()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some test data
        Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        
        $approvedJob->approve();
        $rejectedJob->reject('Test reason');

        $this->artisan('approval:status')
            ->expectsOutput('Laravel Approval Package Status')
            ->expectsOutput('📋 Configuration Check:')
            ->expectsOutput('✅ Configuration file loaded')
            ->expectsOutput('🗄️ Database Check:')
            ->expectsOutput('✅ Approvals table exists')
            ->expectsOutput('📈 Statistics:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_show_approval_status_for_specific_model()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some test data
        Job::create(['title' => 'Test Job', 'description' => 'Test']);

        $this->artisan('approval:status', ['--model' => Job::class])
            ->expectsOutput('Laravel Approval Package Status')
            ->assertExitCode(0);
    }
} 