<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\Company;
use LaravelApproval\Tests\Models\Post;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Facades\Approval;
use Illuminate\Support\Facades\Auth;

class FacadeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_approve_model_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue(Approval::approve($job));
        $this->assertTrue($job->isApproved());
    }

    /** @test */
    public function it_can_reject_model_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue(Approval::reject($job, 'Spam content'));
        $this->assertTrue($job->isRejected());
    }

    /** @test */
    public function it_can_set_pending_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $job->approve();
        $this->assertTrue($job->isApproved());

        $this->assertTrue(Approval::setPending($job));
        $this->assertTrue($job->isPending());
    }

    /** @test */
    public function it_can_get_status_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertEquals('pending', Approval::getStatus($job));

        $job->approve();
        $this->assertEquals('approved', Approval::getStatus($job));

        $job->reject('Test reason');
        $this->assertEquals('rejected', Approval::getStatus($job));
    }

    /** @test */
    public function it_can_check_approval_status_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test Description',
        ]);

        $this->assertTrue(Approval::isPending($job));
        $this->assertFalse(Approval::isApproved($job));
        $this->assertFalse(Approval::isRejected($job));

        $job->approve();
        $this->assertFalse(Approval::isPending($job));
        $this->assertTrue(Approval::isApproved($job));
        $this->assertFalse(Approval::isRejected($job));

        $job->reject('Test reason');
        $this->assertFalse(Approval::isPending($job));
        $this->assertFalse(Approval::isApproved($job));
        $this->assertTrue(Approval::isRejected($job));
    }

    /** @test */
    public function it_can_get_rejection_reason_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $article->reject('Test rejection reason');
        $this->assertEquals('Test rejection reason', Approval::getRejectionReason($article));
    }

    /** @test */
    public function it_can_get_pending_count_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job 1', 'description' => 'Test']);
        Job::create(['title' => 'Pending Job 2', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $approvedJob->approve();

        $this->assertEquals(2, Approval::getPendingCount(Job::class));
    }

    /** @test */
    public function it_can_get_approved_count_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob1 = Job::create(['title' => 'Approved Job 1', 'description' => 'Test']);
        $approvedJob2 = Job::create(['title' => 'Approved Job 2', 'description' => 'Test']);
        $approvedJob1->approve();
        $approvedJob2->approve();

        $this->assertEquals(2, Approval::getApprovedCount(Job::class));
    }

    /** @test */
    public function it_can_get_rejected_count_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $rejectedJob1 = Job::create(['title' => 'Rejected Job 1', 'description' => 'Test']);
        $rejectedJob2 = Job::create(['title' => 'Rejected Job 2', 'description' => 'Test']);
        $rejectedJob1->reject('Spam');
        $rejectedJob2->reject('Inappropriate');

        $this->assertEquals(2, Approval::getRejectedCount(Job::class));
    }

    /** @test */
    public function it_can_get_pending_models_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job 1', 'description' => 'Test']);
        Job::create(['title' => 'Pending Job 2', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $approvedJob->approve();

        $pendingJobs = Approval::getPendingModels(Job::class);

        $this->assertCount(2, $pendingJobs);
        $this->assertTrue($pendingJobs->every(fn($job) => $job->isPending()));
    }

    /** @test */
    public function it_can_get_approved_models_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob1 = Job::create(['title' => 'Approved Job 1', 'description' => 'Test']);
        $approvedJob2 = Job::create(['title' => 'Approved Job 2', 'description' => 'Test']);
        $approvedJob1->approve();
        $approvedJob2->approve();

        $approvedJobs = Approval::getApprovedModels(Job::class);

        $this->assertCount(2, $approvedJobs);
        $this->assertTrue($approvedJobs->every(fn($job) => $job->isApproved()));
    }

    /** @test */
    public function it_can_get_rejected_models_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $rejectedJob1 = Job::create(['title' => 'Rejected Job 1', 'description' => 'Test']);
        $rejectedJob2 = Job::create(['title' => 'Rejected Job 2', 'description' => 'Test']);
        $rejectedJob1->reject('Spam');
        $rejectedJob2->reject('Inappropriate');

        $rejectedJobs = Approval::getRejectedModels(Job::class);

        $this->assertCount(2, $rejectedJobs);
        $this->assertTrue($rejectedJobs->every(fn($job) => $job->isRejected()));
    }

    /** @test */
    public function it_can_limit_models_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create multiple jobs
        for ($i = 1; $i <= 5; $i++) {
            $job = Job::create(['title' => "Job $i", 'description' => 'Test']);
            $job->approve();
        }

        $limitedJobs = Approval::getApprovedModels(Job::class, 3);

        $this->assertCount(3, $limitedJobs);
    }

    /** @test */
    public function it_can_get_statistics_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        Job::create(['title' => 'Pending Job 1', 'description' => 'Test']);
        Job::create(['title' => 'Pending Job 2', 'description' => 'Test']);
        $approvedJob1 = Job::create(['title' => 'Approved Job 1', 'description' => 'Test']);
        $approvedJob2 = Job::create(['title' => 'Approved Job 2', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        
        $approvedJob1->approve();
        $approvedJob2->approve();
        $rejectedJob->reject('Spam');

        $stats = Approval::getStatistics(Job::class);

        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(2, $stats['approved']);
        $this->assertEquals(1, $stats['rejected']);
        $this->assertEquals(5, $stats['total']);
    }

    /** @test */
    public function it_can_get_all_statistics_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        $job->approve();

        // Create companies
        $company = Company::create(['name' => 'Test Company', 'description' => 'Test']);
        $company->reject('Spam');

        $allStats = Approval::getAllStatistics();

        $this->assertArrayHasKey(Job::class, $allStats);
        $this->assertArrayHasKey(Company::class, $allStats);
        $this->assertEquals(1, $allStats[Job::class]['approved']);
        $this->assertEquals(1, $allStats[Company::class]['rejected']);
    }

    /** @test */
    public function it_can_get_approval_records_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);
        $article->approve();

        $records = Approval::getApprovalRecords();

        $this->assertCount(1, $records);
        $this->assertEquals('approved', $records->first()->status);
    }

    /** @test */
    public function it_can_get_approval_records_with_filters_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article1 = Article::create(['title' => 'Article 1', 'content' => 'Test']);
        $article2 = Article::create(['title' => 'Article 2', 'content' => 'Test']);
        
        $article1->approve();
        $article2->reject('Spam');

        $approvedRecords = Approval::getApprovalRecords(Article::class, 'approved');
        $rejectedRecords = Approval::getApprovalRecords(Article::class, 'rejected');

        $this->assertCount(1, $approvedRecords);
        $this->assertCount(1, $rejectedRecords);
        $this->assertEquals('approved', $approvedRecords->first()->status);
        $this->assertEquals('rejected', $rejectedRecords->first()->status);
    }

    /** @test */
    public function it_can_get_approval_records_with_pagination_via_facade()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create multiple articles
        for ($i = 1; $i <= 10; $i++) {
            $article = Article::create(['title' => "Article $i", 'content' => 'Test']);
            $article->approve();
        }

        $paginatedRecords = Approval::getApprovalRecordsPaginated(Article::class, 'approved', 5);

        $this->assertEquals(10, $paginatedRecords->total());
        $this->assertEquals(5, $paginatedRecords->perPage());
        $this->assertCount(5, $paginatedRecords->items());
    }
} 