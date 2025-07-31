<?php

use Illuminate\Support\Facades\Config;
use LaravelApproval\Enums\ApprovalStatus;
use Tests\Models\Post;

beforeEach(function () {
    // Ensure a clean slate before each test
    Config::set('approvals.default.default_status_for_unaudited', null);
    Config::set('approvals.models', []);
});

test('unaudited model has null status by default', function () {
    $post = Post::factory()->create();

    expect($post->getApprovalStatus())->toBeNull();
    expect($post->isApproved())->toBeFalse();
    expect($post->isPending())->toBeFalse();
    expect($post->isRejected())->toBeFalse();
});

test('approved scope does not include unaudited models when default is null', function () {
    Post::create(['title' => 'Test Post', 'content' => 'Test Content']);

    expect(Post::approved()->count())->toBe(0);
});

test('unaudited model is considered approved when configured', function () {
    Config::set('approvals.default.default_status_for_unaudited', 'approved');

    $post = Post::factory()->create();

    expect($post->getApprovalStatus())->toBe(ApprovalStatus::APPROVED);
    expect($post->isApproved())->toBeTrue();
    expect($post->isPending())->toBeFalse();
});

test('approved scope includes unaudited models when configured', function () {
    Config::set('approvals.default.default_status_for_unaudited', 'approved');

    Post::create(['title' => 'Test Post', 'content' => 'Test Content']);

    // With global scope active, this might be tricky. Let's test the local scope.
    expect(Post::approved()->count())->toBe(1);
});

test('unaudited model is considered pending when configured', function () {
    Config::set('approvals.default.default_status_for_unaudited', 'pending');

    $post = Post::factory()->create();

    expect($post->getApprovalStatus())->toBe(ApprovalStatus::PENDING);
    expect($post->isPending())->toBeTrue();
    expect($post->isApproved())->toBeFalse();
});

test('pending scope includes unaudited models when configured', function () {
    Config::set('approvals.default.default_status_for_unaudited', 'pending');

    Post::create(['title' => 'Test Post', 'content' => 'Test Content']);

    expect(Post::pending()->count())->toBe(1);
});

test('model-specific config overrides default unaudited status', function () {
    Config::set('approvals.default.default_status_for_unaudited', 'rejected');
    Config::set('approvals.models', [
        Post::class => [
            'default_status_for_unaudited' => 'approved',
        ],
    ]);

    $post = Post::factory()->create();

    expect($post->getApprovalStatus())->toBe(ApprovalStatus::APPROVED);
    expect($post->isApproved())->toBeTrue();
    expect($post->isRejected())->toBeFalse(); // Make sure default is not used

    expect(Post::approved()->count())->toBe(1);
    expect(Post::rejected()->count())->toBe(0);
});
