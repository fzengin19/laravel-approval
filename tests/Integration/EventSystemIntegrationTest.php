<?php

use Illuminate\Support\Facades\Event;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    config(['approvals.default.events_enabled' => true]);
    config(['approvals.default.events_logging' => true]);
    $this->user = User::factory()->create();
});

it('can dispatch and handle ModelApproved event', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);

    Event::fake([ModelApproved::class]);

    // Perform the action that should trigger the event
    $post->approve($this->user->id);

    // Assert the event was dispatched
    Event::assertDispatched(ModelApproved::class);
});

it('can dispatch and handle ModelRejected event', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);

    Event::fake([ModelRejected::class]);

    $post->reject($this->user->id, 'spam', 'This is spam content');

    Event::assertDispatched(ModelRejected::class);
});

it('can dispatch and handle ModelPending event', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);

    Event::fake([ModelPending::class]);

    $post->setPending($this->user->id);

    Event::assertDispatched(ModelPending::class);
});

it('can handle multiple events in sequence', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);

    Event::fake([ModelPending::class, ModelApproved::class]);

    $post->setPending($this->user->id);
    $post->approve($this->user->id);

    Event::assertDispatched(ModelPending::class);
    Event::assertDispatched(ModelApproved::class);
});

it('can handle events with context and metadata', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);

    Event::fake([ModelApproved::class]);

    $post->approve($this->user->id);

    Event::assertDispatched(ModelApproved::class, function ($event) use ($post) {
        return $event->model->id === $post->id;
    });
});
