<?php

use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;
use Tests\Models\User;

// Test için Post modelini Approvable trait'i ile genişlet

beforeEach(function () {
    $user = User::factory()->create();

    // Create approved post
    $approvedPost = Post::factory()->create();
    Approval::factory()->create([
        'approvable_type' => $approvedPost->getMorphClass(),
        'approvable_id' => $approvedPost->id,
        'status' => ApprovalStatus::APPROVED,
        'caused_by_type' => $user->getMorphClass(),
        'caused_by_id' => $user->id,
    ]);

    // Create pending post
    $pendingPost = Post::factory()->create();
    Approval::factory()->create([
        'approvable_type' => $pendingPost->getMorphClass(),
        'approvable_id' => $pendingPost->id,
        'status' => ApprovalStatus::PENDING,
        'caused_by_type' => $user->getMorphClass(),
        'caused_by_id' => $user->id,
    ]);
});

it('shows only approved posts when show_only_approved_by_default is true', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $posts = Post::all();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->isApproved())->toBeTrue();
});

it('shows all posts when show_only_approved_by_default is false', function () {
    config(['approvals.default.show_only_approved_by_default' => false]);

    $posts = Post::all();

    expect($posts)->toHaveCount(2);
});

it('can include unapproved posts with withUnapproved scope', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $posts = Post::withUnapproved()->get();

    expect($posts)->toHaveCount(2);
});

it('uses model-specific config for show_only_approved_by_default', function () {
    config([
        'approvals.default.show_only_approved_by_default' => false,
        'approvals.models' => [
            Post::class => [
                'show_only_approved_by_default' => true,
            ],
        ],
    ]);

    $posts = Post::all();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->isApproved())->toBeTrue();
});
