<?php

use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = new Post();
    $this->approval = new Approval(['status' => 'rejected']);
});

it('can create ModelRejected event', function () {
    $event = new ModelRejected($this->post, $this->approval, 1, 'spam', 'This is spam');

    expect($event->model)->toBe($this->post);
    expect($event->approval)->toBe($this->approval);
    expect($event->causedBy)->toBe(1);
    expect($event->reason)->toBe('spam');
    expect($event->comment)->toBe('This is spam');
});

it('can handle null reason and comment', function () {
    $event = new ModelRejected($this->post, $this->approval, 1, null, null);
    
    expect($event->reason)->toBeNull();
    expect($event->comment)->toBeNull();
});

it('can get context and metadata', function () {
    $context = ['key' => 'value'];
    $metadata = ['version' => '1.0'];
    $event = new ModelRejected($this->post, $this->approval, 1, 'spam', 'Comment', $context, $metadata);

    expect($event->context)->toBe($context);
    expect($event->metadata)->toBe($metadata);
}); 