<?php

use LaravelApproval\Enums\ApprovalStatus;
use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->post = Post::factory()->create();
    $this->user = User::factory()->create();
});

it('returns correct status when approved', function () {
    $this->post->approve($this->user->id);

    expect($this->post->isApproved())->toBeTrue();
    expect($this->post->isPending())->toBeFalse();
    expect($this->post->isRejected())->toBeFalse();
    expect($this->post->getApprovalStatus())->toBe(ApprovalStatus::APPROVED);
});

it('returns correct status when rejected', function () {
    $this->post->reject($this->user->id, 'spam', 'This is spam');

    expect($this->post->isApproved())->toBeFalse();
    expect($this->post->isPending())->toBeFalse();
    expect($this->post->isRejected())->toBeTrue();
    expect($this->post->getApprovalStatus())->toBe(ApprovalStatus::REJECTED);
});

it('returns correct status when pending', function () {
    $this->post->setPending($this->user->id);

    expect($this->post->isApproved())->toBeFalse();
    expect($this->post->isPending())->toBeTrue();
    expect($this->post->isRejected())->toBeFalse();
    expect($this->post->getApprovalStatus())->toBe(ApprovalStatus::PENDING);
});

it('returns false for all status methods when no approval exists', function () {
    expect($this->post->isApproved())->toBeFalse();
    expect($this->post->isPending())->toBeFalse();
    expect($this->post->isRejected())->toBeFalse();
    expect($this->post->getApprovalStatus())->toBeNull();
});
