<?php

use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Listeners\HandleModelApproved;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.default.events_webhooks_enabled' => true,
    ]);

    $this->post = new Post;
    $this->approval = new Approval(['status' => 'approved']);

    $this->webhookDispatcher = Mockery::mock(WebhookDispatcher::class);
    $this->listener = new HandleModelApproved($this->webhookDispatcher);
});

it('can handle ModelApproved event and dispatch webhooks', function (?string $comment) {
    $event = new ModelApproved($this->post, $this->approval, 1, $comment);

    $this->webhookDispatcher
        ->shouldReceive('dispatchWebhooks')
        ->once()
        ->with('model_approved', $this->post, $event);

    $this->listener->handle($event);
})->with([
    'test comment',
    null,
]);
