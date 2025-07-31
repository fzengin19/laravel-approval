<?php

use LaravelApproval\Events\ModelPending;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = new Post();
    $this->approval = new Approval(['status' => 'pending']);
});

it('can create ModelPending event', function () {
    $event = new ModelPending($this->post, $this->approval, 1, 'Initial creation');

    expect($event->model)->toBe($this->post);
    expect($event->approval)->toBe($this->approval);
    expect($event->causedBy)->toBe(1);
    expect($event->comment)->toBe('Initial creation');
});

it('can handle null comment', function () {
    $event = new ModelPending($this->post, $this->approval, 1, null);
    
    expect($event->comment)->toBeNull();
});

it('can get context and metadata', function () {
    $context = ['key' => 'value'];
    $metadata = ['version' => '1.0'];
    $event = new ModelPending($this->post, $this->approval, 1, 'Comment', $context, $metadata);

    expect($event->context)->toBe($context);
    expect($event->metadata)->toBe($metadata);
}); 