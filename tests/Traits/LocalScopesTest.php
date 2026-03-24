<?php

use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    // Set up rejection reasons configuration for testing
    config(['approvals.default.rejection_reasons' => [
        'inappropriate_content' => 'Inappropriate Content',
        'spam' => 'Spam',
        'duplicate' => 'Duplicate',
        'incomplete' => 'Incomplete',
        'other' => 'Other',
    ]]);

    $this->user = User::factory()->create();

    // Create test posts
    $this->post1 = Post::create(['title' => 'Post 1', 'content' => 'Content 1']);
    $this->post2 = Post::create(['title' => 'Post 2', 'content' => 'Content 2']);
    $this->post3 = Post::create(['title' => 'Post 3', 'content' => 'Content 3']);
});

it('can scope to approved posts', function () {
    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $approvedPosts = Post::approved()->get();

    expect($approvedPosts)->toHaveCount(1);
    expect($approvedPosts->first()->id)->toBe($this->post1->id);
});

it('can scope to pending posts', function () {
    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $pendingPosts = Post::pending()->get();

    expect($pendingPosts)->toHaveCount(1);
    expect($pendingPosts->first()->id)->toBe($this->post3->id);
});

it('can scope to pending posts when approved-only visibility is enabled', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $pendingPosts = Post::pending()->get();

    expect($pendingPosts)->toHaveCount(1);
    expect($pendingPosts->first()->id)->toBe($this->post3->id);
});

it('can scope to rejected posts', function () {
    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $rejectedPosts = Post::rejected()->get();

    expect($rejectedPosts)->toHaveCount(1);
    expect($rejectedPosts->first()->id)->toBe($this->post2->id);
});

it('can scope to rejected posts when approved-only visibility is enabled', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $rejectedPosts = Post::rejected()->get();

    expect($rejectedPosts)->toHaveCount(1);
    expect($rejectedPosts->first()->id)->toBe($this->post2->id);
});

it('can scope with approval status', function () {
    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $posts = Post::withApprovalStatus()->get();

    expect($posts)->toHaveCount(3);

    // Check that latestApproval relationship is loaded
    foreach ($posts as $post) {
        expect($post->relationLoaded('latestApproval'))->toBeTrue();
    }
});

it('can scope with unapproved posts', function () {
    $this->post1->approve($this->user->id);
    $this->post2->reject($this->user->id, 'spam', 'This is spam');
    $this->post3->setPending($this->user->id);

    $unapprovedPosts = Post::withUnapproved()->get();

    expect($unapprovedPosts)->toHaveCount(3); // All posts should be included
});
