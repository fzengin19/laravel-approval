<?php

use LaravelApproval\Facades\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class FacadeTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = FacadeTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('can approve a model through facade', function () {
    $approval = Approval::approve($this->post, 1);

    expect($approval)->toBeInstanceOf(\LaravelApproval\Models\Approval::class);
    expect($approval->status)->toBe('approved');
    expect($approval->caused_by)->toBe(1);
});

it('can reject a model through facade', function () {
    $approval = Approval::reject($this->post, 1, 'Invalid content', 'Content violates guidelines');

    expect($approval)->toBeInstanceOf(\LaravelApproval\Models\Approval::class);
    expect($approval->status)->toBe('rejected');
    expect($approval->caused_by)->toBe(1);
    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe('Invalid content - Content violates guidelines');
});

it('can set pending through facade', function () {
    $approval = Approval::setPending($this->post, 1);

    expect($approval)->toBeInstanceOf(\LaravelApproval\Models\Approval::class);
    expect($approval->status)->toBe('pending');
    expect($approval->caused_by)->toBe(1);
});

it('can get statistics for a model class', function () {
    // Create approved post
    $approvedPost = FacadeTestPost::create(['title' => 'Approved', 'content' => 'Content']);
    $approvedPost->approve(1);

    // Create pending post
    $pendingPost = FacadeTestPost::create(['title' => 'Pending', 'content' => 'Content']);
    $pendingPost->setPending(1);

    // Reddedilmiş post oluştur
    $rejectedPost = FacadeTestPost::create(['title' => 'Rejected', 'content' => 'Content']);
    $rejectedPost->reject(1, 'Invalid');

    $statistics = Approval::getStatistics(FacadeTestPost::class);

    expect($statistics)->toHaveKeys(['total', 'approved', 'pending', 'rejected', 'approved_percentage', 'pending_percentage', 'rejected_percentage']);
    expect($statistics['total'])->toBe(4); // 3 yeni + 1 beforeEach'den
    expect($statistics['approved'])->toBe(1);
    expect($statistics['pending'])->toBe(1);
    expect($statistics['rejected'])->toBe(1);
});

it('can get all statistics', function () {
    config(['approvals.models' => [FacadeTestPost::class => []]]);

    $approvedPost = FacadeTestPost::create(['title' => 'Approved', 'content' => 'Content']);
    $approvedPost->approve(1);

    $allStatistics = Approval::getAllStatistics();

    expect($allStatistics)->toHaveKey(FacadeTestPost::class);
    expect($allStatistics[FacadeTestPost::class])->toHaveKeys(['total', 'approved', 'pending', 'rejected', 'approved_percentage', 'pending_percentage', 'rejected_percentage']);
});
