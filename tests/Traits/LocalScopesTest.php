<?php

use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    // Set up rejection reasons configuration for testing
    config(['approvals.default.rejection_reasons' => [
        'inappropriate_content' => 'Inappropriate Content',
        'spam' => 'Spam',
        'duplicate' => 'Duplicate',
        'incomplete' => 'Incomplete',
        'other' => 'Other',
    ]]);

    // Create test posts
    $this->post1 = Post::create(['title' => 'Post 1', 'content' => 'Content 1']);
    $this->post2 = Post::create(['title' => 'Post 2', 'content' => 'Content 2']);
    $this->post3 = Post::create(['title' => 'Post 3', 'content' => 'Content 3']);
});

it('can scope to approved posts', function () {
    $this->post1->approve(1);
    $this->post2->reject(1, 'spam', 'This is spam');
    $this->post3->setPending(1);

    $approvedPosts = Post::approved()->get();

    expect($approvedPosts)->toHaveCount(1);
    expect($approvedPosts->first()->id)->toBe($this->post1->id);
});

it('can scope to pending posts', function () {
    $this->post1->approve(1);
    $this->post2->reject(1, 'spam', 'This is spam');
    $this->post3->setPending(1);

    $pendingPosts = Post::pending()->get();

    expect($pendingPosts)->toHaveCount(1);
    expect($pendingPosts->first()->id)->toBe($this->post3->id);
});

it('can scope to rejected posts', function () {
    $this->post1->approve(1);
    $this->post2->reject(1, 'spam', 'This is spam');
    $this->post3->setPending(1);

    $rejectedPosts = Post::rejected()->get();

    expect($rejectedPosts)->toHaveCount(1);
    expect($rejectedPosts->first()->id)->toBe($this->post2->id);
});

it('can scope with approval status', function () {
    $this->post1->approve(1);
    $this->post2->reject(1, 'spam', 'This is spam');
    $this->post3->setPending(1);

    $posts = Post::withApprovalStatus()->get();

    expect($posts)->toHaveCount(3);
    
    // Check that latestApproval relationship is loaded
    foreach ($posts as $post) {
        expect($post->relationLoaded('latestApproval'))->toBeTrue();
    }
});

it('can scope with unapproved posts', function () {
    $this->post1->approve(1);
    $this->post2->reject(1, 'spam', 'This is spam');
    $this->post3->setPending(1);

    $unapprovedPosts = Post::withUnapproved()->get();

    expect($unapprovedPosts)->toHaveCount(3); // All posts should be included
});
