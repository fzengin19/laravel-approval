<?php

use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class GlobalScopeTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    // Create approved post
    $approvedPost = GlobalScopeTestPost::create([
        'title' => 'Approved Post',
        'content' => 'Content',
    ]);

    Approval::create([
        'approvable_type' => GlobalScopeTestPost::class,
        'approvable_id' => $approvedPost->id,
        'status' => 'approved',
        'caused_by' => 1,
    ]);

    // Create pending post
    $pendingPost = GlobalScopeTestPost::create([
        'title' => 'Pending Post',
        'content' => 'Content',
    ]);

    Approval::create([
        'approvable_type' => GlobalScopeTestPost::class,
        'approvable_id' => $pendingPost->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);
});

it('shows only approved posts when show_only_approved_by_default is true', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $posts = GlobalScopeTestPost::all();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->title)->toBe('Approved Post');
});

it('shows all posts when show_only_approved_by_default is false', function () {
    config(['approvals.default.show_only_approved_by_default' => false]);

    $posts = GlobalScopeTestPost::all();

    expect($posts)->toHaveCount(2);
});

it('can include unapproved posts with withUnapproved scope', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $posts = GlobalScopeTestPost::withUnapproved()->get();

    expect($posts)->toHaveCount(2);
});

it('uses model-specific config for show_only_approved_by_default', function () {
    config([
        'approvals.default.show_only_approved_by_default' => false,
        'approvals.models' => [
            GlobalScopeTestPost::class => [
                'show_only_approved_by_default' => true,
            ],
        ],
    ]);

    $posts = GlobalScopeTestPost::all();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->title)->toBe('Approved Post');
});
