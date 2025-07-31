<?php

use Illuminate\Support\Facades\Auth;
use LaravelApproval\Enums\ApprovalStatus;
use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->post = Post::factory()->create();
    $this->user = User::factory()->create();
    $this->anotherUser = User::factory()->create();
});

it('creates new approval record in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $this->post->setPending($this->user->id);

    expect($this->post->isPending())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_id' => $this->post->id,
        'status' => ApprovalStatus::PENDING->value,
        'caused_by_id' => $this->user->id,
        'caused_by_type' => $this->user->getMorphClass(),
    ]);
    $firstApprovalId = $this->post->latestApproval->id;

    // A second call should create a new record
    $this->post->setPending($this->anotherUser->id);

    $this->post->refresh();

    expect($this->post->approvals()->count())->toBe(2);
    expect($this->post->latestApproval->id)->not->toBe($firstApprovalId);
});

it('updates existing approval record in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $this->post->setPending($this->user->id);

    expect($this->post->isPending())->toBeTrue();
    $firstApprovalId = $this->post->latestApproval->id;
    $this->assertDatabaseHas('approvals', [
        'id' => $firstApprovalId,
        'caused_by_id' => $this->user->id,
    ]);

    // A second call should update the existing record
    $this->post->setPending($this->anotherUser->id);
    expect($this->post->approvals()->count())->toBe(1);
    expect($this->post->latestApproval->id)->toBe($firstApprovalId);
    $this->assertDatabaseHas('approvals', [
        'id' => $firstApprovalId,
        'caused_by_id' => $this->anotherUser->id,
    ]);
});

it('uses authenticated user id when caused_by is null', function () {
    config(['approvals.default.mode' => 'insert']);
    Auth::login($this->user);

    $this->post->setPending();

    expect($this->post->isPending())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_id' => $this->post->id,
        'caused_by_id' => $this->user->id,
        'caused_by_type' => $this->user->getMorphClass(),
    ]);
});
