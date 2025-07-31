<?php

use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Listeners\HandleModelRejected;
use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.default.events_webhooks_enabled' => true
    ]);

    $this->post = new Post();
    $this->approval = new Approval(['status' => 'rejected']);
    
    $this->webhookDispatcher = Mockery::mock(WebhookDispatcher::class);
    $this->listener = new HandleModelRejected($this->webhookDispatcher);
});

it('can handle ModelRejected event and dispatch webhooks', function (?string $reason, ?string $comment) {
    $event = new ModelRejected($this->post, $this->approval, 1, $reason, $comment);
    
    $this->webhookDispatcher
        ->shouldReceive('dispatchWebhooks')
        ->once()
        ->with('model_rejected', $this->post, $event);
    
    $this->listener->handle($event);
})->with([
    ['test reason', 'test comment'],
    [null, null],
]); 