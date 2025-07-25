<?php

use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class SmartRejectTestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    // Set up rejection reasons configuration for testing
    config(['approvals.rejection_reasons' => [
        'inappropriate_content' => 'Inappropriate Content',
        'spam' => 'Spam',
        'duplicate' => 'Duplicate',
        'incomplete' => 'Incomplete',
        'other' => 'Other',
    ]]);

    $this->post = SmartRejectTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('uses predefined reason when reason is a config key', function () {
    $approval = $this->post->reject(1, 'spam', 'Additional details');

    expect($approval->rejection_reason)->toBe('spam');
    expect($approval->rejection_comment)->toBe('Additional details');
});

it('uses other as reason when reason is not a config key', function () {
    $approval = $this->post->reject(1, 'Custom rejection reason', 'Additional details');

    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe('Custom rejection reason - Additional details');
});

it('handles custom reason without additional comment', function () {
    $approval = $this->post->reject(1, 'Custom rejection reason');

    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe('Custom rejection reason');
});

it('handles null reason', function () {
    $approval = $this->post->reject(1, null, 'Only comment');

    expect($approval->rejection_reason)->toBeNull();
    expect($approval->rejection_comment)->toBe('Only comment');
});

it('handles all predefined reasons correctly', function () {
    $predefinedReasons = ['inappropriate_content', 'spam', 'duplicate', 'incomplete', 'other'];

    foreach ($predefinedReasons as $reason) {
        $approval = $this->post->reject(1, $reason, 'Test comment');
        
        expect($approval->rejection_reason)->toBe($reason);
        expect($approval->rejection_comment)->toBe('Test comment');
    }
});

it('works in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $approval = $this->post->reject(1, 'spam', 'Test comment');

    expect($approval->rejection_reason)->toBe('spam');
    expect($approval->rejection_comment)->toBe('Test comment');
    expect($this->post->approvals()->count())->toBe(1);
});

it('works in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->reject(1, 'spam', 'Test comment');

    expect($approval->rejection_reason)->toBe('spam');
    expect($approval->rejection_comment)->toBe('Test comment');
    expect($this->post->approvals()->count())->toBe(1);
});

it('handles case sensitivity correctly', function () {
    // Test that exact key matching is case-sensitive
    $approval = $this->post->reject(1, 'SPAM', 'Test comment');

    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe('SPAM - Test comment');
});

it('handles empty string reason', function () {
    $approval = $this->post->reject(1, '', 'Test comment');

    expect($approval->rejection_reason)->toBe('other');
    expect($approval->rejection_comment)->toBe(' - Test comment');
}); 