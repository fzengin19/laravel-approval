<?php

use Illuminate\Support\Facades\Event;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Traits\HasApprovals;
use Workbench\App\Models\Post;

// Test için Post modelini HasApprovals trait'i ile genişlet
class EventsTestPost extends Post
{
    use HasApprovals;

    protected $table = 'posts';
}

beforeEach(function () {
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
               $event->approval->rejection_reason === 'Invalid content' &&
               $event->approval->rejection_comment === 'Content violates guidelines';
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
