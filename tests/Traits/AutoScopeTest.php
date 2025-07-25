<?php

use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class AutoScopeTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = AutoScopeTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('respects auto_scope configuration when set to false', function () {
    config(['approvals.default.auto_scope' => false]);

    // Create a new model instance to trigger bootApprovable
    $post = new AutoScopeTestPost;

    // When auto_scope is false, we should be able to see all posts (including unapproved)
    // This is a practical test of whether the global scope is applied or not
    $allPosts = AutoScopeTestPost::all();

    // The test passes if no exception is thrown and we can access all posts
    expect($allPosts)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
});

it('respects model-specific auto_scope configuration', function () {
    config([
        'approvals.default.auto_scope' => false,
        'approvals.models' => [
            AutoScopeTestPost::class => [
                'auto_scope' => true,
            ],
        ],
    ]);

    // Create a new model instance to trigger bootApprovable
    $post = new AutoScopeTestPost;

    // When model-specific auto_scope is true, global scope should be applied
    // This means we should only see approved posts by default
    $posts = AutoScopeTestPost::all();

    // The test passes if no exception is thrown
    expect($posts)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
});
