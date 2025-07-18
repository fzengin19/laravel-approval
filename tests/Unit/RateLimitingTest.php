<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable rate limiting for tests
        Config::set('approval.rate_limiting.enabled', true);
        Config::set('approval.rate_limiting.max_attempts', 2);
        Config::set('approval.rate_limiting.decay_minutes', 1);
    }

    /** @test */
    public function it_respects_rate_limiting_for_approve_actions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // First approve should work
        $this->assertTrue($job->approve());

        // Second approve should work
        $this->assertTrue($job->approve());

        // Third approve should be rate limited
        $this->expectException(ThrottleRequestsException::class);
        $job->approve();
    }

    /** @test */
    public function it_respects_rate_limiting_for_reject_actions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // First reject should work
        $this->assertTrue($job->reject('spam'));

        // Second reject should work
        $this->assertTrue($job->reject('inappropriate'));

        // Third reject should be rate limited
        $this->expectException(ThrottleRequestsException::class);
        $job->reject('duplicate');
    }

    /** @test */
    public function it_respects_rate_limiting_for_pending_actions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // First setPending should work
        $this->assertTrue($job->setPending());

        // Second setPending should work
        $this->assertTrue($job->setPending());

        // Third setPending should be rate limited
        $this->expectException(ThrottleRequestsException::class);
        $job->setPending();
    }

    /** @test */
    public function it_does_not_apply_rate_limiting_when_disabled()
    {
        Config::set('approval.rate_limiting.enabled', false);

        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Multiple actions should work without rate limiting
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($job->approve());
            $this->assertTrue($job->reject('spam'));
            $this->assertTrue($job->setPending());
        }
    }

    /** @test */
    public function it_uses_correct_rate_limit_key()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Clear any existing rate limit
        RateLimiter::clear('approval_approve_' . $user->id);

        // First approve should work
        $this->assertTrue($job->approve());

        // Check that the rate limit was hit (should not be too many attempts yet)
        $this->assertFalse(RateLimiter::tooManyAttempts('approval_approve_' . $user->id, 2));
    }

    /** @test */
    public function it_handles_guest_users_for_rate_limiting()
    {
        // Clear any existing rate limit for guest
        RateLimiter::clear('approval_approve_guest');

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // First approve should work
        $this->assertTrue($job->approve());

        // Second approve should work
        $this->assertTrue($job->approve());

        // Third approve should be rate limited
        $this->expectException(ThrottleRequestsException::class);
        $job->approve();
    }
} 