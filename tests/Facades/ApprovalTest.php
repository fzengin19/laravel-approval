<?php

use LaravelApproval\Facades\Approval;
use LaravelApproval\Traits\HasApprovals;
use Workbench\App\Models\Post;

// Test için Post modelini HasApprovals trait'i ile genişlet
class FacadeTestPost extends Post
{
    use HasApprovals;

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
    expect($approval->rejection_reason)->toBe('Invalid content');
    expect($approval->rejection_comment)->toBe('Content violates guidelines');
});

it('can set pending through facade', function () {
    $approval = Approval::setPending($this->post, 1);

    expect($approval)->toBeInstanceOf(\LaravelApproval\Models\Approval::class);
    expect($approval->status)->toBe('pending');
    expect($approval->caused_by)->toBe(1);
});

it('can get statistics for a model class', function () {
    // Onaylı post oluştur
    $approvedPost = FacadeTestPost::create(['title' => 'Approved', 'content' => 'Content']);
    $approvedPost->approve(1);

    // Beklemede post oluştur
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
