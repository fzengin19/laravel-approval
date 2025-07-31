<?php

use LaravelApproval\Events\ModelSettingPending;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = new Post();
    $this->approval = new Approval(['status' => 'pending']);
});

it('can create ModelSettingPending event', function () {
    $event = new ModelSettingPending($this->post, $this->approval, 1, 'Manually set to pending');

    expect($event->model)->toBe($this->post);
    expect($event->approval)->toBe($this->approval);
    expect($event->causedBy)->toBe(1);
    expect($event->comment)->toBe('Manually set to pending');
});

it('can handle null comment', function () {
    $event = new ModelSettingPending($this->post, $this->approval, 1, null);
    
    expect($event->comment)->toBeNull();
});

it('can get context and metadata', function () {
    $context = ['key' => 'value'];
    $metadata = ['version' => '1.0'];
    $event = new ModelSettingPending($this->post, $this->approval, 1, 'Comment', $context, $metadata);

    expect($event->context)->toBe($context);
    expect($event->metadata)->toBe($metadata);
}); 