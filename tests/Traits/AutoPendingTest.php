<?php

use LaravelApproval\Traits\HasApprovals;
use Workbench\App\Models\Post;

// Test için Post modelini HasApprovals trait'i ile genişlet
class AutoPendingTestPost extends Post
{
    use HasApprovals;

    protected $table = 'posts';
}

it('creates pending approval when auto_pending_on_create is true', function () {
    config(['approvals.default.auto_pending_on_create' => true]);

    $post = AutoPendingTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);

    expect($post->approvals()->count())->toBe(1);
    expect($post->approvals()->first()->status)->toBe('pending');
    expect($post->isPending())->toBeTrue();
});

it('does not create approval when auto_pending_on_create is false', function () {
    config(['approvals.default.auto_pending_on_create' => false]);

    $post = AutoPendingTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);

    expect($post->approvals()->count())->toBe(0);
    expect($post->isPending())->toBeFalse();
});
