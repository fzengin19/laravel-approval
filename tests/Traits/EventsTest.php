<?php

use Illuminate\Support\Facades\Event;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class EventsTestPost extends Post
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

    $this->post = EventsTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('dispatches ModelPending event when setPending is called', function () {
    Event::fake();

    config(['approvals.default.events' => true]);

    $this->post->setPending(1);

    Event::assertDispatched(ModelPending::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'pending';
    });
});

it('dispatches ModelApproved event when approve is called', function () {
    Event::fake();

    config(['approvals.default.events' => true]);

    $this->post->approve(1);

    Event::assertDispatched(ModelApproved::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'approved';
    });
});

it('dispatches ModelRejected event when reject is called', function () {
    Event::fake();

    config(['approvals.default.events' => true]);

    $this->post->reject(1, 'Invalid content', 'Content violates guidelines');

    Event::assertDispatched(ModelRejected::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'rejected' &&
               $event->approval->rejection_reason === 'other' &&
               $event->approval->rejection_comment === 'Invalid content - Content violates guidelines';
    });
});

it('does not dispatch events when events config is false', function () {
    Event::fake();

    config(['approvals.default.events' => false]);

    $this->post->setPending(1);
    $this->post->approve(1);
    $this->post->reject(1, 'Invalid content', 'Content violates guidelines');

    Event::assertNotDispatched(ModelPending::class);
    Event::assertNotDispatched(ModelApproved::class);
    Event::assertNotDispatched(ModelRejected::class);
});

it('dispatches events in upsert mode', function () {
    Event::fake();

    config([
        'approvals.default.events' => true,
        'approvals.default.mode' => 'upsert',
    ]);

    // setPending in upsert mode
    $this->post->setPending(1);
    Event::assertDispatched(ModelPending::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'pending';
    });

    // approve in upsert mode
    $this->post->approve(1);
    Event::assertDispatched(ModelApproved::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'approved';
    });

    // reject in upsert mode
    $this->post->reject(1, 'Invalid content', 'Content violates guidelines');
    Event::assertDispatched(ModelRejected::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'rejected' &&
               $event->approval->rejection_reason === 'other' &&
               $event->approval->rejection_comment === 'Invalid content - Content violates guidelines';
    });
});

it('dispatches events in insert mode', function () {
    Event::fake();

    config([
        'approvals.default.events' => true,
        'approvals.default.mode' => 'insert',
    ]);

    // setPending in insert mode
    $this->post->setPending(1);
    Event::assertDispatched(ModelPending::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'pending';
    });

    // approve in insert mode
    $this->post->approve(1);
    Event::assertDispatched(ModelApproved::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'approved';
    });

    // reject in insert mode
    $this->post->reject(1, 'Invalid content', 'Content violates guidelines');
    Event::assertDispatched(ModelRejected::class, function ($event) {
        return $event->model->id === $this->post->id &&
               $event->approval->status === 'rejected' &&
               $event->approval->rejection_reason === 'other' &&
               $event->approval->rejection_comment === 'Invalid content - Content violates guidelines';
    });
});
