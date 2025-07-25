<?php

use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class StatusMethodsTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = StatusMethodsTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('returns false for all status methods when no approval exists', function () {
    expect($this->post->isApproved())->toBeFalse();
    expect($this->post->isPending())->toBeFalse();
    expect($this->post->isRejected())->toBeFalse();
    expect($this->post->getApprovalStatus())->toBeNull();
});

it('returns correct status when approved', function () {
    Approval::create([
        'approvable_type' => StatusMethodsTestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'approved',
        'caused_by' => 1,
    ]);

    expect($this->post->isApproved())->toBeTrue();
    expect($this->post->isPending())->toBeFalse();
    expect($this->post->isRejected())->toBeFalse();
    expect($this->post->getApprovalStatus())->toBe('approved');
});

it('returns correct status when pending', function () {
    Approval::create([
        'approvable_type' => StatusMethodsTestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    expect($this->post->isApproved())->toBeFalse();
    expect($this->post->isPending())->toBeTrue();
    expect($this->post->isRejected())->toBeFalse();
    expect($this->post->getApprovalStatus())->toBe('pending');
});

it('returns correct status when rejected', function () {
    Approval::create([
        'approvable_type' => StatusMethodsTestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'rejected',
        'caused_by' => 1,
    ]);

    expect($this->post->isApproved())->toBeFalse();
    expect($this->post->isPending())->toBeFalse();
    expect($this->post->isRejected())->toBeTrue();
    expect($this->post->getApprovalStatus())->toBe('rejected');
});
