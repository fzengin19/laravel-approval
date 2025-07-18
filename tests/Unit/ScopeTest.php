<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Job;
use LaravelApproval\Tests\Models\Company;
use LaravelApproval\Tests\Models\Post;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Scopes\ConfigurableScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class ScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_scope_approved_jobs()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $approvedJob->approve();

        $approvedJobs = Job::approved()->get();

        $this->assertCount(1, $approvedJobs);
        $this->assertTrue($approvedJobs->first()->isApproved());
        $this->assertEquals('Approved Job', $approvedJobs->first()->title);
    }

    /** @test */
    public function it_can_scope_pending_jobs()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $approvedJob->approve();

        $pendingJobs = Job::pending()->get();

        $this->assertCount(1, $pendingJobs);
        $this->assertTrue($pendingJobs->first()->isPending());
        $this->assertEquals('Pending Job', $pendingJobs->first()->title);
    }

    /** @test */
    public function it_can_scope_rejected_jobs()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        $rejectedJob->reject('Spam');

        $rejectedJobs = Job::rejected()->get();

        $this->assertCount(1, $rejectedJobs);
        $this->assertTrue($rejectedJobs->first()->isRejected());
        $this->assertEquals('Rejected Job', $rejectedJobs->first()->title);
    }

    /** @test */
    public function it_can_scope_approved_companies()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create companies with different statuses
        $pendingCompany = Company::create(['name' => 'Pending Company', 'description' => 'Test']);
        $approvedCompany = Company::create(['name' => 'Approved Company', 'description' => 'Test']);
        $approvedCompany->approve();

        $approvedCompanies = Company::approved()->get();

        $this->assertCount(1, $approvedCompanies);
        $this->assertTrue($approvedCompanies->first()->isApproved());
        $this->assertEquals('Approved Company', $approvedCompanies->first()->name);
    }

    /** @test */
    public function it_can_scope_approved_posts()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create posts with different statuses
        $pendingPost = Post::create(['title' => 'Pending Post', 'content' => 'Test']);
        $approvedPost = Post::create(['title' => 'Approved Post', 'content' => 'Test']);
        $approvedPost->approve();

        $approvedPosts = Post::approved()->get();

        $this->assertCount(1, $approvedPosts);
        $this->assertTrue($approvedPosts->first()->isApproved());
        $this->assertEquals('Approved Post', $approvedPosts->first()->title);
    }

    /** @test */
    public function it_can_scope_approved_articles_with_pivot()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create articles with different statuses
        $pendingArticle = Article::create(['title' => 'Pending Article', 'content' => 'Test']);
        $approvedArticle = Article::create(['title' => 'Approved Article', 'content' => 'Test']);
        $approvedArticle->approve();

        $approvedArticles = Article::approved()->get();

        $this->assertCount(1, $approvedArticles);
        $this->assertTrue($approvedArticles->first()->isApproved());
        $this->assertEquals('Approved Article', $approvedArticles->first()->title);
    }

    /** @test */
    public function it_can_scope_pending_articles_with_pivot()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create articles with different statuses
        $pendingArticle = Article::create(['title' => 'Pending Article', 'content' => 'Test']);
        $approvedArticle = Article::create(['title' => 'Approved Article', 'content' => 'Test']);
        $approvedArticle->approve();

        $pendingArticles = Article::pending()->get();

        $this->assertCount(1, $pendingArticles);
        $this->assertTrue($pendingArticles->first()->isPending());
        $this->assertEquals('Pending Article', $pendingArticles->first()->title);
    }

    /** @test */
    public function it_can_scope_rejected_articles_with_pivot()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create articles with different statuses
        $pendingArticle = Article::create(['title' => 'Pending Article', 'content' => 'Test']);
        $rejectedArticle = Article::create(['title' => 'Rejected Article', 'content' => 'Test']);
        $rejectedArticle->reject('Inappropriate content');

        $rejectedArticles = Article::rejected()->get();

        $this->assertCount(1, $rejectedArticles);
        $this->assertTrue($rejectedArticles->first()->isRejected());
        $this->assertEquals('Rejected Article', $rejectedArticles->first()->title);
    }

    /** @test */
    public function it_can_scope_with_approval_status()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);
        $article->approve();

        $articlesWithApproval = Article::withApprovalStatus()->get();

        $this->assertCount(1, $articlesWithApproval);
        $this->assertTrue($articlesWithApproval->first()->relationLoaded('approval'));
    }

    /** @test */
    public function configurable_scope_does_not_apply_when_disabled()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // Create jobs with different statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $approvedJob->approve();

        // Since auto_scope is disabled in test config, all jobs should be returned
        $allJobs = Job::all();

        $this->assertCount(2, $allJobs);
    }

    /** @test */
    public function configurable_scope_can_be_configured()
    {
        // Test with different configurations
        Config::set('approval.models.' . Job::class . '.show_only_approved_by_default', true);
        
        $job = Job::create(['title' => 'Test Job', 'description' => 'Test']);
        
        // Test that the configuration is set correctly
        $config = Config::get('approval.models.' . Job::class);
        $this->assertTrue($config['show_only_approved_by_default']);
        
        $job->approve();
        $this->assertCount(1, Job::all());
    }

    /** @test */
    public function only_approved_scope_works_correctly()
    {
        // Create jobs with different statuses
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        $rejectedJob = Job::create(['title' => 'Rejected Job', 'description' => 'Test']);
        
        $approvedJob->approve();
        $rejectedJob->reject('Test reason');

        // Only approved scope should only return approved jobs
        $approvedJobs = Job::withoutGlobalScopes()->approved()->get();
        $this->assertCount(1, $approvedJobs);
        $this->assertEquals('Approved Job', $approvedJobs->first()->title);
    }

    /** @test */
    public function configurable_scope_with_show_only_approved_disabled()
    {
        Config::set('approval.models.' . Job::class . '.show_only_approved_by_default', false);
        
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        
        $approvedJob->approve();

        // Should show all jobs when show_only_approved_by_default is false
        $this->assertCount(2, Job::all());
    }

    /** @test */
    public function configurable_scope_with_auto_scope_disabled()
    {
        Config::set('approval.models.' . Job::class . '.auto_scope', false);
        
        $pendingJob = Job::create(['title' => 'Pending Job', 'description' => 'Test']);
        $approvedJob = Job::create(['title' => 'Approved Job', 'description' => 'Test']);
        
        $approvedJob->approve();

        // Should show all jobs when auto_scope is disabled
        $this->assertCount(2, Job::all());
    }
} 