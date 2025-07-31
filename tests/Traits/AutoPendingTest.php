<?php

use LaravelApproval\Enums\ApprovalStatus;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet

it('creates pending approval when auto_pending_on_create is true', function () {
    config(['approvals.default.auto_pending_on_create' => true]);

    $post = Post::factory()->create();

    expect($post->approvals()->count())->toBe(1);
    expect($post->approvals()->first()->status)->toBe(ApprovalStatus::PENDING);
    expect($post->isPending())->toBeTrue();
});

it('does not create approval when auto_pending_on_create is false', function () {
    config(['approvals.default.auto_pending_on_create' => false]);

    $post = Post::factory()->create();

    expect($post->approvals()->count())->toBe(0);
    expect($post->isPending())->toBeFalse();
});
