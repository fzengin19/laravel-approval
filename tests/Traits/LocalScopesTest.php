<?php

use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class LocalScopesTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    // Create 3 approved posts
    for ($i = 1; $i <= 3; $i++) {
        $post = LocalScopesTestPost::create([
            'title' => "Approved Post {$i}",
            'content' => "Content {$i}",
        ]);

        Approval::create([
            'approvable_type' => LocalScopesTestPost::class,
            'approvable_id' => $post->id,
            'status' => 'approved',
            'caused_by' => 1,
        ]);
    }

    // Create 2 pending posts
    for ($i = 1; $i <= 2; $i++) {
        $post = LocalScopesTestPost::create([
            'title' => "Pending Post {$i}",
            'content' => "Content {$i}",
        ]);

        Approval::create([
            'approvable_type' => LocalScopesTestPost::class,
            'approvable_id' => $post->id,
            'status' => 'pending',
            'caused_by' => 1,
        ]);
    }

    // 1 reddedilmiş post oluştur
    $post = LocalScopesTestPost::create([
        'title' => 'Rejected Post',
        'content' => 'Content',
    ]);

    Approval::create([
        'approvable_type' => LocalScopesTestPost::class,
        'approvable_id' => $post->id,
        'status' => 'rejected',
        'caused_by' => 1,
    ]);
});

it('can scope to approved posts', function () {
    $approvedPosts = LocalScopesTestPost::approved()->get();

    expect($approvedPosts)->toHaveCount(3);
    expect($approvedPosts->pluck('title')->toArray())->toContain('Approved Post 1');
    expect($approvedPosts->pluck('title')->toArray())->toContain('Approved Post 2');
    expect($approvedPosts->pluck('title')->toArray())->toContain('Approved Post 3');
});

it('can scope to pending posts', function () {
    $pendingPosts = LocalScopesTestPost::pending()->get();

    expect($pendingPosts)->toHaveCount(2);
    expect($pendingPosts->pluck('title')->toArray())->toContain('Pending Post 1');
    expect($pendingPosts->pluck('title')->toArray())->toContain('Pending Post 2');
});

it('can scope to rejected posts', function () {
    $rejectedPosts = LocalScopesTestPost::rejected()->get();

    expect($rejectedPosts)->toHaveCount(1);
    expect($rejectedPosts->first()->title)->toBe('Rejected Post');
});

it('can scope with approval status', function () {
    $posts = LocalScopesTestPost::withApprovalStatus()->get();

    expect($posts)->toHaveCount(6);
    expect($posts->first()->latestApproval)->not->toBeNull();
});

it('can scope with unapproved posts', function () {
    $posts = LocalScopesTestPost::withUnapproved()->get();

    expect($posts)->toHaveCount(6);
    // This scope removes the global scope, so all posts should be visible
});
