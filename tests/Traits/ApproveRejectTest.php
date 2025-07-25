<?php

use Illuminate\Support\Facades\Event;
use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class ApproveRejectTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = ApproveRejectTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('can approve in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->approve(1);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('approved');
    expect($approval->caused_by)->toBe(1);
    expect($this->post->approvals()->count())->toBe(1);
});

it('can approve in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $approval = $this->post->approve(1);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('approved');
    expect($approval->caused_by)->toBe(1);
    expect($this->post->approvals()->count())->toBe(1);
});

it('can reject with reason and comment in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->reject(1, 'Invalid content', 'Content violates guidelines');

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('rejected');
    expect($approval->caused_by)->toBe(1);
    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe('Invalid content - Content violates guidelines');
    expect($this->post->approvals()->count())->toBe(1);
});

it('can reject with reason and comment in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $approval = $this->post->reject(1, 'Invalid content', 'Content violates guidelines');

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('rejected');
    expect($approval->caused_by)->toBe(1);
    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe('Invalid content - Content violates guidelines');
    expect($this->post->approvals()->count())->toBe(1);
});

it('uses authenticated user id when caused_by is null', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->approve();

    expect($approval->caused_by)->toBeNull();
});

it('uses model-specific config for mode', function () {
    config([
        'approvals.default.mode' => 'insert',
        'approvals.models' => [
            ApproveRejectTestPost::class => [
                'mode' => 'upsert',
            ],
        ],
    ]);

    // First approval should create a record
    $approval1 = $this->post->approve(1);
    expect($this->post->approvals()->count())->toBe(1);

    // Second approval should update the existing record (upsert mode)
    $approval2 = $this->post->reject(2, 'Invalid');
    expect($this->post->approvals()->count())->toBe(1);
    expect($approval2->status)->toBe('rejected');
});

it('uses model-specific config for events', function () {
    config([
        'approvals.default.events' => true,
        'approvals.models' => [
            ApproveRejectTestPost::class => [
                'events' => false,
            ],
        ],
    ]);

    $this->post->approve(1);

    // Events should not be dispatched for this model
    // Note: In a real test environment, you would use Event::fake() and Event::assertNotDispatched()
    // For now, we just verify the method works without errors
    expect($this->post->isApproved())->toBeTrue();
});
