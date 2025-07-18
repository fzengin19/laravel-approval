<?php

namespace LaravelApproval\Tests\Feature;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\Company;
use LaravelApproval\Tests\Models\Post;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Facades\Approval;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;

class IntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_can_handle_complete_approval_workflow_for_column_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create job
        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        // Check initial status
        $this->assertTrue($job->isPending());
        $this->assertEquals('pending', $job->getApprovalStatus());

        // Approve job
        $this->assertTrue($job->approve());
        $this->assertTrue($job->isApproved());
        $this->assertEquals('approved', $job->getApprovalStatus());
        $this->assertNotNull($job->approved_at);

        // Reject job
        $this->assertTrue($job->reject('Spam content'));
        $this->assertTrue($job->isRejected());
        $this->assertEquals('rejected', $job->getApprovalStatus());

        // Set back to pending
        $this->assertTrue($job->setPending());
        $this->assertTrue($job->isPending());
        $this->assertEquals('pending', $job->getApprovalStatus());

        // Verify events were dispatched
        Event::assertDispatched(ModelApproved::class);
        Event::assertDispatched(ModelRejected::class);
        Event::assertDispatched(ModelPending::class);
    }

    /** @test */
    public function it_can_handle_complete_approval_workflow_for_pivot_based_model()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create article
        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        // Check initial status
        $this->assertTrue($article->isPending());
        $this->assertEquals('pending', $article->getApprovalStatus());

        // Approve article
        $this->assertTrue($article->approve());
        $this->assertTrue($article->isApproved());
        $this->assertEquals('approved', $article->getApprovalStatus());

        // Check database record
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);

        // Reject article
        $this->assertTrue($article->reject('Inappropriate content'));
        $this->assertTrue($article->isRejected());
        $this->assertEquals('rejected', $article->getApprovalStatus());
        $this->assertEquals('Inappropriate content', $article->getRejectionReason());

        // Check database record updated
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'rejected',
            'rejection_reason' => 'Inappropriate content',
        ]);

        // Set back to pending
        $this->assertTrue($article->setPending());
        $this->assertTrue($article->isPending());
        $this->assertEquals('pending', $article->getApprovalStatus());

        // Verify events were dispatched
        Event::assertDispatched(ModelApproved::class);
        Event::assertDispatched(ModelRejected::class);
        Event::assertDispatched(ModelPending::class);
    }

    /** @test */
    public function it_can_handle_multiple_models_with_different_approval_methods()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create models with different approval methods
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']); // approved_at column
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']); // is_approved column
        $post = Post::create(['title' => 'Test Post', 'content' => 'Test']); // approval_status column
        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']); // pivot table

        // Approve all models
        $job->approve();
        $company->approve();
        $post->approve();
        $article->approve();

        // Verify all are approved
        $this->assertTrue($job->isApproved());
        $this->assertTrue($company->isApproved());
        $this->assertTrue($post->isApproved());
        $this->assertTrue($article->isApproved());

        // Verify different column values
        $this->assertNotNull($job->approved_at);
        $this->assertTrue($company->is_approved);
        $this->assertEquals('approved', $post->approval_status);

        // Verify pivot table record
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function it_can_use_scopes_with_different_approval_methods()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);

        $approvedJob->approve();
        $rejectedJob->reject('Spam');

        // Test scopes
        $this->assertCount(1, Job::pending()->get());
        $this->assertCount(1, Job::approved()->get());
        $this->assertCount(1, Job::rejected()->get());

        // Create articles with different statuses
        $pendingArticle = Article::create(['title' => 'Pending Article', 'content' => 'Test']);
        $approvedArticle = Article::create(['title' => 'Approved Article', 'content' => 'Test']);
        $rejectedArticle = Article::create(['title' => 'Rejected Article', 'content' => 'Test']);

        $approvedArticle->approve();
        $rejectedArticle->reject('Inappropriate');

        // Test scopes for pivot-based model
        $this->assertCount(1, Article::pending()->get());
        $this->assertCount(1, Article::approved()->get());
        $this->assertCount(1, Article::rejected()->get());
    }

    /** @test */
    public function it_can_use_facade_for_all_operations()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Use facade for all operations
        $this->assertTrue(Approval::approve($job));
        $this->assertTrue(Approval::isApproved($job));
        $this->assertEquals('approved', Approval::getStatus($job));

        $this->assertTrue(Approval::reject($job, 'Test reason'));
        $this->assertTrue(Approval::isRejected($job));
        $this->assertEquals('rejected', Approval::getStatus($job));

        $this->assertTrue(Approval::setPending($job));
        $this->assertTrue(Approval::isPending($job));
        $this->assertEquals('pending', Approval::getStatus($job));
    }

    /** @test */
    public function it_can_get_statistics_for_multiple_models()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job 1', 'description' => 'Test']);
        Job::create(['title' => 'Pending Job 2', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        
        $approvedJob->approve();
        $rejectedJob->reject('Spam');

        // Create companies with different statuses
        Company::create(['name' => 'Pending Company', 'description' => 'Test']);
        $approvedCompany = Company::create(['name' => 'Approved Company', 'description' => 'Test']);
        $approvedCompany->approve();

        // Get statistics
        $jobStats = Approval::getStatistics(Job::class);
        $companyStats = Approval::getStatistics(Company::class);
        $allStats = Approval::getAllStatistics();

        // Verify job statistics
        $this->assertEquals(2, $jobStats['pending']);
        $this->assertEquals(1, $jobStats['approved']);
        $this->assertEquals(1, $jobStats['rejected']);
        $this->assertEquals(4, $jobStats['total']);

        // Verify company statistics
        $this->assertEquals(1, $companyStats['pending']);
        $this->assertEquals(1, $companyStats['approved']);
        $this->assertEquals(0, $companyStats['rejected']);
        $this->assertEquals(2, $companyStats['total']);

        // Verify all statistics
        $this->assertArrayHasKey(Job::class, $allStats);
        $this->assertArrayHasKey(Company::class, $allStats);
    }

    /** @test */
    public function it_can_handle_approval_records_with_pagination()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create multiple articles
        for ($i = 1; $i <= 15; $i++) {
            $article = Article::create(['title' => "Article $i", 'content' => 'Test']);
            if ($i <= 10) {
                $article->approve();
            } else {
                $article->reject('Spam');
            }
        }

        // Test pagination
        $approvedRecords = Approval::getApprovalRecordsPaginated(Article::class, 'approved', 5);
        $rejectedRecords = Approval::getApprovalRecordsPaginated(Article::class, 'rejected', 5);

        $this->assertEquals(10, $approvedRecords->total());
        $this->assertEquals(5, $approvedRecords->perPage());
        $this->assertCount(5, $approvedRecords->items());

        $this->assertEquals(5, $rejectedRecords->total());
        $this->assertEquals(5, $rejectedRecords->perPage());
        $this->assertCount(5, $rejectedRecords->items());
    }

    /** @test */
    public function it_can_handle_approval_with_specific_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Auth::login($user1);

        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);

        // Approve with specific user
        $this->assertTrue($job->approve($user2->id));

        // Verify event has correct user
        Event::assertDispatched(ModelApproved::class, function ($event) use ($user2) {
            return $event->getApprovedBy() === $user2->id;
        });

        // Reject with specific user
        $this->assertTrue($job->reject('Test reason', $user2->id));

        Event::assertDispatched(ModelRejected::class, function ($event) use ($user2) {
            return $event->getRejectedBy() === $user2->id;
        });
    }

    /** @test */
    public function it_can_handle_approval_relationship_loading()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);
        $article->approve();

        // Load with approval relationship
        $articlesWithApproval = Article::withApprovalStatus()->get();

        $this->assertCount(1, $articlesWithApproval);
        $this->assertTrue($articlesWithApproval->first()->relationLoaded('approval'));
        $this->assertInstanceOf(\LaravelApproval\Models\Approval::class, $articlesWithApproval->first()->approval);
    }

    /** @test */
    public function it_can_handle_approval_records_with_relationships()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);
        $article->approve();

        $records = Approval::getApprovalRecords(null, 'approved');

        $this->assertCount(1, $records);
        $this->assertTrue($records->first()->relationLoaded('approvable'));
        $this->assertTrue($records->first()->relationLoaded('approver'));
        $this->assertInstanceOf(Article::class, $records->first()->approvable);
        $this->assertInstanceOf(User::class, $records->first()->approver);
    }
} 