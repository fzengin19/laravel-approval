<?php

use Illuminate\Support\Facades\Event;
use LaravelApproval\Enums\ApprovalStatus;
use Tests\Models\Post;
use Illuminate\Support\Facades\Auth;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelApproving;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelRejecting;
use Tests\Models\User;

beforeEach(function () {
    $this->post = Post::factory()->create();
    $this->user = User::factory()->create();
    // Another user for upsert tests
    $this->anotherUser = User::factory()->create();
});

it('can approve in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $this->post->approve($this->user->id);

    expect($this->post->isApproved())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_type' => $this->post->getMorphClass(),
        'approvable_id' => $this->post->id,
        'status' => ApprovalStatus::APPROVED->value,
        'caused_by_id' => $this->user->id,
        'caused_by_type' => $this->user->getMorphClass(),
    ]);
    expect($this->post->approvals()->count())->toBe(1);
});

it('can approve in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $this->post->approve($this->user->id);

    expect($this->post->isApproved())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_type' => $this->post->getMorphClass(),
        'approvable_id' => $this->post->id,
        'status' => ApprovalStatus::APPROVED->value,
        'caused_by_id' => $this->user->id,
        'caused_by_type' => $this->user->getMorphClass(),
    ]);
    expect($this->post->approvals()->count())->toBe(1);
});

it('can reject with reason and comment in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $this->post->reject($this->user->id, 'Invalid content', 'Content violates guidelines');

    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_type' => $this->post->getMorphClass(),
        'approvable_id' => $this->post->id,
        'status' => ApprovalStatus::REJECTED->value,
        'caused_by_id' => $this->user->id,
        'caused_by_type' => $this->user->getMorphClass(),
        'rejection_reason' => 'other',
        'rejection_comment' => 'Invalid content - Content violates guidelines',
    ]);
    expect($this->post->approvals()->count())->toBe(1);
});

it('uses authenticated user id when caused_by is null', function () {
    config(['approvals.default.mode' => 'insert']);
    Auth::login($this->user);

    $this->post->approve();

    expect($this->post->isApproved())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_type' => $this->post->getMorphClass(),
        'approvable_id' => $this->post->id,
        'caused_by_id' => $this->user->id,
        'caused_by_type' => $this->user->getMorphClass(),
    ]);
});

it('uses model-specific config for mode', function () {
    config([
        'approvals.default.mode' => 'insert',
        'approvals.models' => [
            Post::class => [
                'mode' => 'upsert',
            ],
        ],
    ]);

    // First approval should create a record
    $this->post->approve($this->user->id);
    $this->post->refresh();
    expect($this->post->approvals()->count())->toBe(1);
    expect($this->post->isApproved())->toBeTrue();

    // Second approval should update the existing record (upsert mode)
    $this->post->reject($this->anotherUser->id, 'Invalid');
    $this->post->refresh();
    expect($this->post->approvals()->count())->toBe(1);
    expect($this->post->isRejected())->toBeTrue();
    $this->assertDatabaseHas('approvals', [
        'approvable_id' => $this->post->id,
        'caused_by_id' => $this->anotherUser->id,
        'status' => ApprovalStatus::REJECTED->value,
    ]);
});

it('uses model-specific config for events', function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.models' => [
            Post::class => [
                'events_enabled' => false,
            ],
        ],
    ]);

    Event::fake([
        ModelApproved::class,
        ModelApproving::class,
        ModelRejected::class,
        ModelRejecting::class,
    ]);

    $this->post->approve($this->user->id);
    $this->post->refresh();

    Event::assertNothingDispatched();
    expect($this->post->isApproved())->toBeTrue();
});
