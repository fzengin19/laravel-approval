<?php

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
    config(['approvals.default.allow_custom_reasons' => false]);

    $this->post = Post::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('uses predefined reason when reason is a config key', function () {
    $this->post->reject(1, 'spam', 'Additional details');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'spam',
        'rejection_comment' => 'Additional details',
    ]);
});

it('uses other as reason when reason is not a config key', function () {
    $this->post->reject(1, 'Custom rejection reason', 'Additional details');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'other',
        'rejection_comment' => 'Custom rejection reason - Additional details',
    ]);
});

it('handles custom reason without additional comment', function () {
    $this->post->reject(1, 'Custom rejection reason');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'other',
        'rejection_comment' => 'Custom rejection reason',
    ]);
});

it('handles null reason', function () {
    $this->post->reject(1, null, 'Only comment');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => null,
        'rejection_comment' => 'Only comment',
    ]);
});

it('handles all predefined reasons correctly', function () {
    $predefinedReasons = ['inappropriate_content', 'spam', 'duplicate', 'incomplete', 'other'];

    foreach ($predefinedReasons as $reason) {
        $this->post->reject(1, $reason, 'Test comment');

        expect($this->post->isRejected())->toBeTrue();
        $this->assertDatabaseHas('approvals', [
            'rejection_reason' => $reason,
            'rejection_comment' => 'Test comment',
        ]);

        // Reset for next iteration
        $this->post->approvals()->delete();
    }
});

it('works in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $this->post->reject(1, 'spam', 'Test comment');

    expect($this->post->isRejected())->toBeTrue();
    expect($this->post->approvals()->count())->toBe(1);
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'spam',
        'rejection_comment' => 'Test comment',
    ]);
});

it('works in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $this->post->reject(1, 'spam', 'Test comment');

    expect($this->post->isRejected())->toBeTrue();
    expect($this->post->approvals()->count())->toBe(1);
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'spam',
        'rejection_comment' => 'Test comment',
    ]);
});

it('handles case sensitivity correctly', function () {
    // Test that exact key matching is case-sensitive
    $this->post->reject(1, 'SPAM', 'Test comment');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'other',
        'rejection_comment' => 'SPAM - Test comment',
    ]);
});

it('handles empty string reason', function () {
    $this->post->reject(1, '', 'Test comment');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'rejection_reason' => 'other',
        'rejection_comment' => 'Test comment',
    ]);
});
