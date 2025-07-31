<?php

use LaravelApproval\Events\ModelApproving;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = new Post();
});

it('can create ModelApproving event', function () {
    $event = new ModelApproving($this->post, 1, 'Test Comment');

    expect($event->model)->toBe($this->post);
    expect($event->causedBy)->toBe(1);
    expect($event->comment)->toBe('Test Comment');
});

it('can handle null comment', function () {
    $event = new ModelApproving($this->post, 1, null);
    
    expect($event->comment)->toBeNull();
});

it('can get context and metadata', function () {
    $context = ['key' => 'value'];
    $metadata = ['version' => '1.0'];
    $event = new ModelApproving($this->post, 1, 'Test Comment', $context, $metadata);

    expect($event->context)->toBe($context);
    expect($event->metadata)->toBe($metadata);
}); 