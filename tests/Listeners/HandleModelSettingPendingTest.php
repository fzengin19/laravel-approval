<?php

use LaravelApproval\Events\ModelSettingPending;
use LaravelApproval\Listeners\HandleModelSettingPending;
use LaravelApproval\Core\WebhookDispatcher;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    config([
        'approvals.default.events_enabled' => true,
        'approvals.default.events_webhooks_enabled' => true
    ]);

    $this->post = new Post();
    $this->approval = new Approval(['status' => 'pending']);
    
    $this->webhookDispatcher = Mockery::mock(WebhookDispatcher::class);
    $this->listener = new HandleModelSettingPending($this->webhookDispatcher);
});

it('can handle ModelSettingPending event and dispatch webhooks', function (?string $comment) {
    $event = new ModelSettingPending($this->post, $this->approval, 1, $comment);
    
    $this->webhookDispatcher
        ->shouldReceive('dispatchWebhooks')
        ->once()
        ->with('model_setting_pending', $this->post, $event);
    
    $this->listener->handle($event);
})->with([
    'test comment',
    null,
]); 