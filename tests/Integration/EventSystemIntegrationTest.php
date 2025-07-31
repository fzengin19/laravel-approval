<?php

use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Listeners\HandleModelApproved;
use LaravelApproval\Listeners\HandleModelRejected;
use LaravelApproval\Listeners\HandleModelPending;
use Tests\Models\Post;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config(['approvals.default.events_enabled' => true]);
    config(['approvals.default.events_logging' => true]);
});

it('can dispatch and handle ModelApproved event', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);
    
    Event::fake([ModelApproved::class]);
    
    // Perform the action that should trigger the event
    $post->approve(1);
    
    // Assert the event was dispatched
    Event::assertDispatched(ModelApproved::class);
});

it('can dispatch and handle ModelRejected event', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);
    
    Event::fake([ModelRejected::class]);
    
    $post->reject(1, 'spam', 'This is spam content');
    
    Event::assertDispatched(ModelRejected::class);
});

it('can dispatch and handle ModelPending event', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);
    
    Event::fake([ModelPending::class]);
    
    $post->setPending(1);
    
    Event::assertDispatched(ModelPending::class);
});

it('can handle multiple events in sequence', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);
    
    Event::fake([ModelPending::class, ModelApproved::class]);
    
    $post->setPending(1);
    $post->approve(1);
    
    Event::assertDispatched(ModelPending::class);
    Event::assertDispatched(ModelApproved::class);
});

it('can handle events with context and metadata', function () {
    $post = Post::create(['title' => 'Test Post', 'content' => 'Content']);
    
    Event::fake([ModelApproved::class]);
    
    $post->approve(1);
    
    Event::assertDispatched(ModelApproved::class, function ($event) use ($post) {
        return $event->model->id === $post->id;
    });
}); 