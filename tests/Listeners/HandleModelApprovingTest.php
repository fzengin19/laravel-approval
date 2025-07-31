<?php

use LaravelApproval\Events\ModelApproving;
use LaravelApproval\Listeners\HandleModelApproving;
use LaravelApproval\Core\WebhookDispatcher;
use Tests\Models\Post;

beforeEach(function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.default.events_webhooks_enabled' => true
    ]);

    $this->post = new Post();
    
    $this->webhookDispatcher = Mockery::mock(WebhookDispatcher::class);
    $this->listener = new HandleModelApproving($this->webhookDispatcher);
});

it('can handle ModelApproving event and dispatch webhooks', function (?string $comment) {
    $event = new ModelApproving($this->post, 1, $comment);
    
    $this->webhookDispatcher
        ->shouldReceive('dispatchWebhooks')
        ->once()
        ->with('model_approving', $this->post, $event);
    
    $this->listener->handle($event);
})->with([
    'test comment',
    null,
]); 