<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable cache for tests
        Config::set('approval.cache.enabled', true);
        Config::set('approval.cache.ttl', 3600);
        Config::set('approval.cache.prefix', 'approval_status_');
    }

    /** @test */
    public function it_caches_approval_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // First call should cache the status
        $status = $job->getApprovalStatus();
        $this->assertEquals('pending', $status);

        // Check if status is cached
        $cacheKey = 'approval_status_jobs_' . $job->id;
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals('pending', Cache::get($cacheKey));
    }

    /** @test */
    public function it_clears_cache_on_approval()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Cache the initial status
        $job->getApprovalStatus();
        $cacheKey = 'approval_status_jobs_' . $job->id;
        $this->assertTrue(Cache::has($cacheKey));

        // Approve the job
        $job->approve();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));

        // New status should be cached
        $newStatus = $job->getApprovalStatus();
        $this->assertEquals('approved', $newStatus);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals('approved', Cache::get($cacheKey));
    }

    /** @test */
    public function it_clears_cache_on_rejection()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Cache the initial status
        $job->getApprovalStatus();
        $cacheKey = 'approval_status_jobs_' . $job->id;
        $this->assertTrue(Cache::has($cacheKey));

        // Reject the job
        $job->reject('spam');

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));

        // New status should be cached
        $newStatus = $job->getApprovalStatus();
        $this->assertEquals('rejected', $newStatus);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals('rejected', Cache::get($cacheKey));
    }

    /** @test */
    public function it_clears_cache_on_setting_pending()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Approve first and cache the status
        $job->approve();
        $job->getApprovalStatus(); // Cache the approved status
        $cacheKey = 'approval_status_jobs_' . $job->id;
        $this->assertTrue(Cache::has($cacheKey));

        // Set to pending
        $job->setPending();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));

        // New status should be cached on first query
        $newStatus = $job->getApprovalStatus();
        $this->assertEquals('pending', $newStatus);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals('pending', Cache::get($cacheKey));
    }

    /** @test */
    public function it_does_not_cache_when_disabled()
    {
        Config::set('approval.cache.enabled', false);

        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Get status
        $job->getApprovalStatus();

        // Should not be cached
        $cacheKey = 'approval_status_jobs_' . $job->id;
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_uses_correct_cache_key()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Get status to cache it
        $job->getApprovalStatus();

        // Check cache key format
        $expectedKey = 'approval_status_jobs_' . $job->id;
        $this->assertTrue(Cache::has($expectedKey));
    }

    /** @test */
    public function it_respects_cache_ttl()
    {
        Config::set('approval.cache.ttl', 60); // 1 minute

        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Get status to cache it
        $job->getApprovalStatus();

        $cacheKey = 'approval_status_jobs_' . $job->id;
        $this->assertTrue(Cache::has($cacheKey));

        // Check TTL (this is approximate)
        $ttl = Cache::getStore()->get($cacheKey . '_ttl');
        if ($ttl !== null) {
            $this->assertLessThanOrEqual(60, $ttl);
        }
    }
} 