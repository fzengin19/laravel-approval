<?php

use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Events\ModelRejecting;
use LaravelApproval\Listeners\HandleModelRejecting;
use Tests\Models\Post;

beforeEach(function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.default.events_webhooks_enabled' => true,
    ]);

    $this->post = new Post;

    $this->webhookDispatcher = Mockery::mock(WebhookDispatcher::class);
    $this->listener = new HandleModelRejecting($this->webhookDispatcher);
});

it('can handle ModelRejecting event and dispatch webhooks', function (?string $reason, ?string $comment) {
    $event = new ModelRejecting($this->post, 1, $reason, $comment);

    $this->webhookDispatcher
        ->shouldReceive('dispatchWebhooks')
        ->once()
        ->with('model_rejecting', $this->post, $event);

    $this->listener->handle($event);
})->with([
    ['test reason', 'test comment'],
    [null, null],
]);
