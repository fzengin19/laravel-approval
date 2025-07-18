<?php

namespace LaravelApproval\Tests\Unit;

use LaravelApproval\Tests\TestCase;
use LaravelApproval\Tests\Models\Article;
use LaravelApproval\Tests\Models\User;
use LaravelApproval\Models\Approval;
use Illuminate\Support\Facades\Auth;

class ApprovalModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_approval_record()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $approval = Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->assertInstanceOf(Approval::class, $approval);
        $this->assertEquals(Article::class, $approval->approvable_type);
        $this->assertEquals($article->id, $approval->approvable_id);
        $this->assertEquals('approved', $approval->status);
        $this->assertEquals($user->id, $approval->approved_by);
    }

    /** @test */
    public function it_can_get_approvable_relationship()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $approval = Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $approvable = $approval->approvable;

        $this->assertInstanceOf(Article::class, $approvable);
        $this->assertEquals($article->id, $approvable->id);
        $this->assertEquals('Test Article', $approvable->title);
    }

    /** @test */
    public function it_can_get_approver_relationship()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Test Content',
        ]);

        $approval = Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $approver = $approval->approver;

        $this->assertInstanceOf(User::class, $approver);
        $this->assertEquals($user->id, $approver->id);
    }

    /** @test */
    public function it_can_scope_approved_records()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article1 = Article::create(['title' => 'Article 1', 'content' => 'Test']);
        $article2 = Article::create(['title' => 'Article 2', 'content' => 'Test']);

        Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article1->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article2->id,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $approvedRecords = Approval::approved()->get();

        $this->assertCount(1, $approvedRecords);
        $this->assertEquals('approved', $approvedRecords->first()->status);
    }

    /** @test */
    public function it_can_scope_pending_records()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article1 = Article::create(['title' => 'Article 1', 'content' => 'Test']);
        $article2 = Article::create(['title' => 'Article 2', 'content' => 'Test']);

        Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article1->id,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article2->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $pendingRecords = Approval::pending()->get();

        $this->assertCount(1, $pendingRecords);
        $this->assertEquals('pending', $pendingRecords->first()->status);
    }

    /** @test */
    public function it_can_scope_rejected_records()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article1 = Article::create(['title' => 'Article 1', 'content' => 'Test']);
        $article2 = Article::create(['title' => 'Article 2', 'content' => 'Test']);

        Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article1->id,
            'status' => 'rejected',
            'rejection_reason' => 'Spam',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article2->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $rejectedRecords = Approval::rejected()->get();

        $this->assertCount(1, $rejectedRecords);
        $this->assertEquals('rejected', $rejectedRecords->first()->status);
    }

    /** @test */
    public function it_can_check_if_approved()
    {
        $approval = new Approval(['status' => 'approved']);
        $this->assertTrue($approval->isApproved());

        $approval = new Approval(['status' => 'pending']);
        $this->assertFalse($approval->isApproved());

        $approval = new Approval(['status' => 'rejected']);
        $this->assertFalse($approval->isApproved());
    }

    /** @test */
    public function it_can_check_if_pending()
    {
        $approval = new Approval(['status' => 'pending']);
        $this->assertTrue($approval->isPending());

        $approval = new Approval(['status' => 'approved']);
        $this->assertFalse($approval->isPending());

        $approval = new Approval(['status' => 'rejected']);
        $this->assertFalse($approval->isPending());
    }

    /** @test */
    public function it_can_check_if_rejected()
    {
        $approval = new Approval(['status' => 'rejected']);
        $this->assertTrue($approval->isRejected());

        $approval = new Approval(['status' => 'approved']);
        $this->assertFalse($approval->isRejected());

        $approval = new Approval(['status' => 'pending']);
        $this->assertFalse($approval->isRejected());
    }

    /** @test */
    public function it_can_get_status()
    {
        $approval = new Approval(['status' => 'approved']);
        $this->assertEquals('approved', $approval->getStatus());

        $approval = new Approval(['status' => 'pending']);
        $this->assertEquals('pending', $approval->getStatus());

        $approval = new Approval(['status' => 'rejected']);
        $this->assertEquals('rejected', $approval->getStatus());
    }

    /** @test */
    public function it_can_get_rejection_reason()
    {
        $approval = new Approval(['rejection_reason' => 'Spam content']);
        $this->assertEquals('Spam content', $approval->getRejectionReason());

        $approval = new Approval(['rejection_reason' => null]);
        $this->assertNull($approval->getRejectionReason());
    }

    /** @test */
    public function it_can_get_approver()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $article = Article::create(['title' => 'Test Article', 'content' => 'Test']);

        $approval = Approval::create([
            'approvable_type' => Article::class,
            'approvable_id' => $article->id,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $approver = $approval->getApprover();

        $this->assertInstanceOf(User::class, $approver);
        $this->assertEquals($user->id, $approver->id);
    }

    /** @test */
    public function it_can_get_approved_at()
    {
        $now = now();
        $approval = new Approval(['approved_at' => $now]);

        $this->assertEquals($now->toDateTimeString(), $approval->getApprovedAt()->toDateTimeString());

        $approval = new Approval(['approved_at' => null]);
        $this->assertNull($approval->getApprovedAt());
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $approval = new Approval();

        $fillable = $approval->getFillable();

        $this->assertContains('approvable_type', $fillable);
        $this->assertContains('approvable_id', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('rejection_reason', $fillable);
        $this->assertContains('approved_by', $fillable);
        $this->assertContains('approved_at', $fillable);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $approval = new Approval();

        $casts = $approval->getCasts();

        $this->assertEquals('datetime', $casts['approved_at']);
        $this->assertEquals('integer', $casts['approved_by']);
    }

    /** @test */
    public function it_has_correct_dates()
    {
        $approval = new Approval();

        $dates = $approval->getDates();

        $this->assertContains('created_at', $dates);
        $this->assertContains('updated_at', $dates);
    }
} 