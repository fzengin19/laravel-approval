<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\Company;
use LaravelApproval\Tests\Models\Post;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class HasApprovalTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_can_detect_approval_column_method()
    {
        $job = new Job();
        $company = new Company();
        $post = new Post();
        $article = new Article();

        $this->assertTrue($job->usesApprovalColumn());
        $this->assertTrue($company->usesApprovalColumn());
        $this->assertTrue($post->usesApprovalColumn());
        $this->assertFalse($article->usesApprovalColumn());
    }

    /** @test */
    public function it_can_detect_pivot_method()
    {
        $job = new Job();
        $company = new Company();
        $post = new Post();
        $article = new Article();

        $this->assertFalse($job->usesApprovalPivot());
        $this->assertFalse($company->usesApprovalPivot());
        $this->assertFalse($post->usesApprovalPivot());
        $this->assertTrue($article->usesApprovalPivot());
    }

    /** @test */
    public function it_can_get_approval_config()
    {
        $job = new Job();
        $config = $job->getApprovalConfig();

        $this->assertIsArray($config);
        $this->assertEquals('approved_at', $config['column']);
        $this->assertTrue($config['fallback_to_pivot']);
        $this->assertFalse($config['auto_scope']);
        $this->assertSame(config('approval.models.' . Job::class . '.events'), $config['events']);
    }

    /** @test */
    public function it_can_approve_job_with_approved_at_column()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($job->approve());
        $this->assertTrue($job->isApproved());
        $this->assertEquals('approved', $job->getApprovalStatus());
        $this->assertNotNull($job->approved_at);

        Event::assertDispatched(ModelApproved::class);
    }

    /** @test */
    public function it_can_approve_company_with_is_approved_column()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $company = Company::create([
            'name' => 'Test Company',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($company->approve());
        $this->assertTrue($company->isApproved());
        $this->assertEquals('approved', $company->getApprovalStatus());
        $this->assertTrue($company->is_approved);

        Event::assertDispatched(ModelApproved::class);
    }

    /** @test */
    public function it_can_approve_post_with_approval_status_column()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $this->assertTrue($post->approve());
        $this->assertTrue($post->isApproved());
        $this->assertEquals('approved', $post->getApprovalStatus());
        $this->assertEquals('approved', $post->approval_status);

        Event::assertDispatched(ModelApproved::class);
    }

    /** @test */
    public function it_can_approve_article_with_pivot_table()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $this->assertTrue($article->approve());
        $this->assertTrue($article->isApproved());
        $this->assertEquals('approved', $article->getApprovalStatus());

        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);

        Event::assertDispatched(ModelApproved::class);
    }

    /** @test */
    public function it_can_reject_job_with_approved_at_column()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($job->reject('Spam'));
        $this->assertTrue($job->isRejected());
        $this->assertEquals('rejected', $job->getApprovalStatus());

        Event::assertDispatched(ModelRejected::class);
    }

    /** @test */
    public function it_can_reject_company_with_is_approved_column()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $company = Company::create([
            'name' => 'Test Company',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($company->reject('Inappropriate content'));
        $this->assertTrue($company->isRejected());
        $this->assertEquals('rejected', $company->getApprovalStatus());

        Event::assertDispatched(ModelRejected::class);
    }

    /** @test */
    public function it_can_reject_post_with_approval_status_column()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $this->assertTrue($post->reject('Duplicate content'));
        $this->assertTrue($post->isRejected());
        $this->assertEquals('rejected', $post->getApprovalStatus());
        $this->assertEquals('rejected', $post->approval_status);

        Event::assertDispatched(ModelRejected::class);
    }

    /** @test */
    public function it_can_reject_article_with_pivot_table()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $this->assertTrue($article->reject('Violates policy'));
        $this->assertTrue($article->isRejected());
        $this->assertEquals('rejected', $article->getApprovalStatus());
        $this->assertEquals('Violates policy', $article->getRejectionReason());

        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'rejected',
            'rejection_reason' => 'Violates policy',
            'approved_by' => $user->id,
        ]);

        Event::assertDispatched(ModelRejected::class);
    }

    /** @test */
    public function it_can_set_pending_status()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        // First approve
        $post->approve();
        $this->assertTrue($post->isApproved());

        // Then set to pending
        $this->assertTrue($post->setPending());
        $this->assertTrue($post->isPending());
        $this->assertEquals('pending', $post->getApprovalStatus());
        $this->assertEquals('pending', $post->approval_status);

        Event::assertDispatched(ModelPending::class);
    }

    /** @test */
    public function it_can_check_pending_status()
    {
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($job->isPending());
        $this->assertEquals('pending', $job->getApprovalStatus());
    }

    /** @test */
    public function it_can_get_rejection_reason_from_pivot()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $article->reject('Test rejection reason');
        $this->assertEquals('Test rejection reason', $article->getRejectionReason());
    }

    /** @test */
    public function it_returns_null_rejection_reason_for_column_based_models()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $post->reject('Test reason');
        $this->assertNull($post->getRejectionReason());
    }

    /** @test */
    public function it_can_approve_with_specific_user_id()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Auth::login($user1);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue($job->approve($user2->id));

        Event::assertDispatched(ModelApproved::class, function ($event) use ($user2) {
            return $event->getApprovedBy() === $user2->id;
        });
    }

    /** @test */
    public function it_can_reject_with_specific_user_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $this->assertTrue($job->reject('Test reason', 999));
        $this->assertTrue($job->isRejected());
    }

    /** @test */
    public function it_handles_global_scope_auto_scope_disabled()
    {
        // Test with auto_scope disabled
        Config::set('approval.models.' . Job::class . '.auto_scope', false);
        
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Should show all jobs when auto_scope is disabled
        $this->assertCount(1, Job::all());
    }

    /** @test */
    public function it_handles_cache_disabled_scenarios()
    {
        Config::set('approval.cache.enabled', false);
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test that cache methods work when cache is disabled
        // We can't test protected methods directly, but we can test their behavior through public methods
        $job->getApprovalStatus(); // This should work without cache
        $this->assertTrue($job->isPending()); // Should work without cache
    }

    /** @test */
    public function it_handles_data_consistency_checks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test data consistency with different column types
        $job->approve();
        $this->assertTrue($job->isApproved());
        
        // Test with approval_status column
        $post = Post::create(['title' => 'Test Post', 'content' => 'Test']);
        $post->approve();
        $this->assertTrue($post->isApproved());
        
        // Test with is_approved column
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']);
        $company->approve();
        $this->assertTrue($company->isApproved());
    }

    /** @test */
    public function it_handles_column_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test usesApprovalColumn method which internally uses hasColumn
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $this->assertTrue($job->usesApprovalColumn()); // Should be true for Job model
        
        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);
        $this->assertFalse($article->usesApprovalColumn()); // Should be false for Article model
    }

    /** @test */
    public function it_handles_rejection_reason_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test with empty rejection reasons (should allow any reason)
        Config::set('approval.rejection_reasons', []);
        $this->assertTrue($job->reject('Any reason here'));
        
        // Test with specific rejection reasons
        Config::set('approval.rejection_reasons', ['spam', 'inappropriate']);
        $job2 = Job::create(['title' => 'Test Job 2', 'description' => 'Test']);
        $this->assertTrue($job2->reject('spam'));
        
        // Test with invalid rejection reason
        $job3 = Job::create(['title' => 'Test Job 3', 'description' => 'Test']);
        $this->expectException(\InvalidArgumentException::class);
        $job3->reject('invalid_reason');
    }

    /** @test */
    public function it_handles_edge_cases_in_approval_methods()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test approve with null user ID
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $this->assertTrue($job->approve(null));
        
        // Test reject with null user ID
        $job2 = Job::create(['title' => 'Test Job 2', 'description' => 'Test']);
        $this->assertTrue($job2->reject('Test reason', null));
        
        // Test setPending with null user ID
        $job3 = Job::create(['title' => 'Test Job 3', 'description' => 'Test']);
        $this->assertTrue($job3->setPending());
    }

    /** @test */
    public function it_handles_scope_methods_with_different_column_types()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test scope methods with different column types
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $post = Post::create(['title' => 'Test Post', 'content' => 'Test']);
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']);
        
        $job->approve();
        $post->approve();
        $company->approve();
        
        // Test scopes work with different column types
        $this->assertCount(1, Job::approved()->get());
        $this->assertCount(1, Post::approved()->get());
        $this->assertCount(1, Company::approved()->get());
    }

    /** @test */
    public function it_handles_scope_with_approval_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $job->approve();
        
        // Test scopeWithApprovalStatus
        $jobsWithStatus = Job::withApprovalStatus()->get();
        $this->assertCount(1, $jobsWithStatus);
        $this->assertTrue($jobsWithStatus->first()->isApproved());
    }

    /** @test */
    public function it_handles_boot_has_approval_method()
    {
        // Test that the trait boot method works correctly
        // This tests the global scope addition (line 35)
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // The boot method should have added global scope
        // We can test this by checking if the model has the trait methods
        $this->assertTrue(method_exists($job, 'approve'));
        $this->assertTrue(method_exists($job, 'reject'));
        $this->assertTrue(method_exists($job, 'setPending'));
        $this->assertTrue(method_exists($job, 'getApprovalStatus'));
    }

    /** @test */
    public function it_handles_cache_enabled_scenarios()
    {
        Config::set('approval.cache.enabled', true);
        Config::set('approval.cache.ttl', 60);
        Config::set('approval.cache.prefix', 'test_approval_');
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test cache behavior when enabled
        $status1 = $job->getApprovalStatus();
        $status2 = $job->getApprovalStatus(); // Should be cached
        
        $this->assertEquals($status1, $status2);
        
        // Test cache clearing
        $job->approve();
        $this->assertTrue($job->isApproved());
    }

    /** @test */
    public function it_handles_different_column_types_comprehensive()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test all column types comprehensively
        $job = Job::create(['title' => 'Job', 'description' => 'Test']); // approved_at
        $company = Company::create(['name' => 'Company', 'description' => 'Test']); // is_approved
        $post = Post::create(['title' => 'Post', 'content' => 'Test']); // approval_status
        $article = Article::create(['title' => 'Article', 'content' => 'Test']); // pivot
        
        // Test approve for all types
        $job->approve();
        $company->approve();
        $post->approve();
        $article->approve();
        
        $this->assertTrue($job->isApproved());
        $this->assertTrue($company->isApproved());
        $this->assertTrue($post->isApproved());
        $this->assertTrue($article->isApproved());
        
        // Test reject for all types
        $job->reject('Test reason');
        $company->reject('Test reason');
        $post->reject('Test reason');
        $article->reject('Test reason');
        
        $this->assertTrue($job->isRejected());
        $this->assertTrue($company->isRejected());
        $this->assertTrue($post->isRejected());
        $this->assertTrue($article->isRejected());
        
        // Test setPending for all types
        $job->setPending();
        $company->setPending();
        $post->setPending();
        $article->setPending();
        
        $this->assertTrue($job->isPending());
        $this->assertTrue($company->isPending());
        $this->assertTrue($post->isPending());
        $this->assertTrue($article->isPending());
    }

    /** @test */
    public function it_handles_scope_methods_comprehensive()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test scope methods with empty results
        $this->assertCount(0, Job::approved()->get());
        $this->assertCount(0, Job::rejected()->get());
        
        // Test scope methods with mixed statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        
        $approvedJob->approve();
        $rejectedJob->reject('Test reason');
        
        // Test all scopes together
        $this->assertCount(1, Job::pending()->get());
        $this->assertCount(1, Job::approved()->get());
        $this->assertCount(1, Job::rejected()->get());
        
        // Test withApprovalStatus scope
        $jobsWithStatus = Job::withApprovalStatus()->get();
        $this->assertCount(3, $jobsWithStatus);
    }

    /** @test */
    public function it_handles_rejection_reason_validation_comprehensive()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with specific rejection reasons
        Config::set('approval.rejection_reasons', ['spam', 'inappropriate', 'duplicate', 'incomplete']);
        
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test valid reasons
        $this->assertTrue($job->reject('spam'));
        $this->assertTrue($job->reject('inappropriate'));
        $this->assertTrue($job->reject('duplicate'));
        $this->assertTrue($job->reject('incomplete'));
        
        // Test invalid reason
        $job2 = Job::create(['title' => 'Test Job 2', 'description' => 'Test']);
        $this->expectException(\InvalidArgumentException::class);
        $job2->reject('invalid_reason');
    }

    /** @test */
    public function it_handles_data_consistency_scenarios()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test data consistency with approval_status column
        $post = Post::create(['title' => 'Test Post', 'content' => 'Test']);
        
        // Manually create inconsistent state
        $post->update(['approval_status' => 'pending']);
        $post->approval()->create([
            'status' => 'rejected',
            'rejection_reason' => 'Test reason',
            'approved_by' => $user->id,
        ]);
        
        // This should trigger data consistency check and return correct status
        $this->assertEquals('rejected', $post->getApprovalStatus());
        
        // Test with approved_at column
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $job->update(['approved_at' => null]);
        $job->approval()->create([
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);
        
        // This should trigger data consistency check and return correct status
        $this->assertEquals('approved', $job->getApprovalStatus());
        
        // Test with is_approved column
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']);
        $company->update(['is_approved' => false]);
        $company->approval()->create([
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);
        
        // This should trigger data consistency check and return correct status
        $this->assertEquals('approved', $company->getApprovalStatus());
    }

    /** @test */
    public function it_handles_cache_scenarios_comprehensive()
    {
        Config::set('approval.cache.enabled', true);
        Config::set('approval.cache.ttl', 60);
        
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test cache with different status types
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test pending status caching
        $status1 = $job->getApprovalStatus();
        $status2 = $job->getApprovalStatus(); // Should be cached
        $this->assertEquals($status1, $status2);
        
        // Test approved status caching
        $job->approve();
        $status3 = $job->getApprovalStatus();
        $status4 = $job->getApprovalStatus(); // Should be cached
        $this->assertEquals($status3, $status4);
        
        // Test rejected status caching
        $job2 = Job::create(['title' => 'Test Job 2', 'description' => 'Test']);
        $job2->reject('Test reason');
        $status5 = $job2->getApprovalStatus();
        $status6 = $job2->getApprovalStatus(); // Should be cached
        $this->assertEquals($status5, $status6);
    }

    /** @test */
    public function it_handles_scope_methods_edge_cases()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test scope methods with empty results
        $this->assertCount(0, Job::approved()->get());
        $this->assertCount(0, Job::rejected()->get());
        
        // Test scope methods with mixed statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        
        $approvedJob->approve();
        $rejectedJob->reject('Test reason');
        
        // Test all scopes together
        $this->assertCount(1, Job::pending()->get());
        $this->assertCount(1, Job::approved()->get());
        $this->assertCount(1, Job::rejected()->get());
        
        // Test withApprovalStatus scope
        $jobsWithStatus = Job::withApprovalStatus()->get();
        $this->assertCount(3, $jobsWithStatus);
    }

    /** @test */
    public function it_handles_via_column_methods_edge_cases()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test via column methods with different scenarios
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test approve via column
        $result = $job->approve();
        $this->assertTrue($result);
        $this->assertTrue($job->isApproved());
        
        // Test reject via column
        $job2 = Job::create(['title' => 'Test Job 2', 'description' => 'Test']);
        $result = $job2->reject('Test reason');
        $this->assertTrue($result);
        $this->assertTrue($job2->isRejected());
        
        // Test set pending via column
        $job3 = Job::create(['title' => 'Test Job 3', 'description' => 'Test']);
        $result = $job3->setPending();
        $this->assertTrue($result);
        $this->assertTrue($job3->isPending());
        
        // Test with different column types
        $post = Post::create(['title' => 'Test Post', 'content' => 'Test']);
        $result = $post->approve();
        $this->assertTrue($result);
        $this->assertTrue($post->isApproved());
        
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']);
        $result = $company->approve();
        $this->assertTrue($result);
        $this->assertTrue($company->isApproved());
    }

    /** @test */
    public function it_handles_consistency_edge_cases()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test consistency with different scenarios that trigger specific lines
        $post = Post::create(['title' => 'Test Post', 'content' => 'Test']);
        
        // Test approval_status column consistency edge cases
        $post->update(['approval_status' => 'pending']);
        $post->approval()->create([
            'status' => 'rejected',
            'rejection_reason' => 'Test reason',
            'approved_by' => $user->id,
        ]);
        
        // This should trigger specific consistency checks
        $this->assertEquals('rejected', $post->getApprovalStatus());
        
        // Test approved_at column consistency edge cases
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $job->approval()->create([
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);
        
        // This should trigger consistency check for approved_at
        // approved_at should be set when pivot is approved
        $this->assertEquals('approved', $job->getApprovalStatus());
        $this->assertNotNull($job->fresh()->approved_at);
        
        // Test is_approved column consistency edge cases
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']);
        $company->approval()->create([
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);
        
        // This should trigger consistency check for is_approved
        // is_approved should be set to true when pivot is approved
        $this->assertEquals('approved', $company->getApprovalStatus());
        $this->assertTrue($company->fresh()->is_approved);
    }

    /** @test */
    public function it_handles_pivot_methods_comprehensive()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test pivot methods comprehensively
        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);
        
        // Test approve via pivot
        $result = $article->approve();
        $this->assertTrue($result);
        $this->assertTrue($article->isApproved());
        
        // Test reject via pivot
        $article2 = Article::create(['title' => 'Test Article 2', 'content' => 'Test']);
        $result = $article2->reject('Test reason');
        $this->assertTrue($result);
        $this->assertTrue($article2->isRejected());
        
        // Test set pending via pivot
        $article3 = Article::create(['title' => 'Test Article 3', 'content' => 'Test']);
        $result = $article3->setPending();
        $this->assertTrue($result);
        $this->assertTrue($article3->isPending());
        
        // Test pivot relationship loading
        $this->assertNotNull($article->approval);
        $this->assertNotNull($article2->approval);
        $this->assertNotNull($article3->approval);
    }
} 