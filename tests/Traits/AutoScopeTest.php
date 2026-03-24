<?php

use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('respects auto_scope configuration when set to false', function () {
    config([
        'approvals.default.auto_scope' => false,
        'approvals.default.show_only_approved_by_default' => true,
    ]);

    Post::clearBootedModels();

    $approvedPost = Post::create(['title' => 'Approved', 'content' => 'Approved']);
    $pendingPost = Post::create(['title' => 'Pending', 'content' => 'Pending']);

    $approvedPost->approve($this->user->id);
    $pendingPost->setPending($this->user->id);

    expect(Post::pluck('id')->all())->toBe([$approvedPost->id, $pendingPost->id]);
});

it('treats auto_scope as a global switch even when a model config tries to override it', function () {
    config([
        'approvals.default.auto_scope' => false,
        'approvals.default.show_only_approved_by_default' => true,
        'approvals.models' => [
            Post::class => [
                'auto_scope' => true,
                'show_only_approved_by_default' => true,
            ],
        ],
    ]);

    Post::clearBootedModels();

    $approvedPost = Post::create(['title' => 'Approved', 'content' => 'Approved']);
    $pendingPost = Post::create(['title' => 'Pending', 'content' => 'Pending']);

    $approvedPost->approve($this->user->id);
    $pendingPost->setPending($this->user->id);

    expect(Post::pluck('id')->all())->toBe([$approvedPost->id, $pendingPost->id]);
});
