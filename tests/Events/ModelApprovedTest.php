<?php

use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = new Post();
    $this->approval = new Approval(['status' => 'approved']);
});

it('can create ModelApproved event', function () {
    $event = new ModelApproved($this->post, $this->approval, 1, 'Test Comment');

    expect($event->model)->toBe($this->post);
    expect($event->approval)->toBe($this->approval);
    expect($event->causedBy)->toBe(1);
    expect($event->comment)->toBe('Test Comment');
});

it('can handle null comment', function () {
    $event = new ModelApproved($this->post, $this->approval, 1, null);
    
    expect($event->comment)->toBeNull();
});

it('can get context and metadata', function () {
    $context = ['key' => 'value'];
    $metadata = ['version' => '1.0'];
    $event = new ModelApproved($this->post, $this->approval, 1, 'Test Comment', $context, $metadata);

    expect($event->context)->toBe($context);
    expect($event->metadata)->toBe($metadata);
}); 