<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use LaravelApproval\Exceptions\ApprovalException;
use LaravelApproval\Exceptions\UnauthorizedApprovalException;
use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelApproving;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelRejecting;
use Tests\Models\Post;
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

it('fails closed when an explicit approval actor does not exist', function () {
    $this->expectException(UnauthorizedApprovalException::class);

    $this->post->approve(999999);
});

it('updates the latest approval record in upsert mode when approval history already exists', function () {
    config(['approvals.default.mode' => 'insert']);

    $this->post->setPending($this->user->id);
    $this->post->approve($this->anotherUser->id);

    $latestApprovalId = $this->post->latestApproval()->firstOrFail()->id;

    config(['approvals.default.mode' => 'upsert']);

    $this->post->reject($this->user->id, 'spam', 'Re-reviewed');

    expect($this->post->approvals()->count())->toBe(2);
    expect($this->post->latestApproval->id)->toBe($latestApprovalId);
    expect($this->post->isRejected())->toBeTrue();
});

it('refreshes the cached latest approval relation after approving', function () {
    $this->post->setPending($this->user->id);
    $this->post->load('latestApproval');

    expect($this->post->isPending())->toBeTrue();

    $this->post->approve($this->anotherUser->id);

    expect($this->post->isApproved())->toBeTrue();
    expect($this->post->latestApproval->status)->toBe(ApprovalStatus::APPROVED);
});

it('rejects unknown reasons when the normalized reason is not configured', function () {
    config(['approvals.default.rejection_reasons' => ['spam' => 'Spam']]);

    $this->expectException(ApprovalException::class);

    $this->post->reject($this->user->id, 'custom_reason', 'Needs explanation');
});
