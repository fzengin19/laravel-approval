<?php

use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Listeners\HandleModelPending;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.default.events_webhooks_enabled' => true,
    ]);

    $this->post = new Post;
    $this->approval = new Approval(['status' => 'pending']);

    $this->webhookDispatcher = Mockery::mock(WebhookDispatcher::class);
    $this->listener = new HandleModelPending($this->webhookDispatcher);
});

it('can handle ModelPending event and dispatch webhooks', function (?string $comment) {
    $event = new ModelPending($this->post, $this->approval, 1, $comment);

    $this->webhookDispatcher
        ->shouldReceive('dispatchWebhooks')
        ->once()
        ->with('model_pending', $this->post, $event);

    $this->listener->handle($event);
})->with([
    'test comment',
    null,
]);
