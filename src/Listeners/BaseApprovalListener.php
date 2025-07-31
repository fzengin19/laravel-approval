<?php

namespace LaravelApproval\Listeners;

use Illuminate\Support\Facades\Log;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Core\WebhookDispatcher;

abstract class BaseApprovalListener
{
    protected WebhookDispatcher $webhookDispatcher;

    public function __construct(WebhookDispatcher $webhookDispatcher)
    {
        $this->webhookDispatcher = $webhookDispatcher;
    }

    protected function dispatchWebhooks(string $eventName, $model, $event): void
    {
        if ($model->getApprovalConfig('events_enabled', true) && $model->getApprovalConfig('events_webhooks_enabled', false)) {
            $this->webhookDispatcher->dispatchWebhooks($eventName, $model, $event);
        }
    }

    protected function logEvent(string $eventName, ApprovableInterface $model, object $event): void
    {
        if ($model->getApprovalConfig('events_enabled', true) && $model->getApprovalConfig('events_logging', false)) {
            $channel = $model->getApprovalConfig('events_logging_channel', null);
            Log::channel($channel)->info("Approval event: {$eventName}", $event->context);
        }
    }

    protected function executeCustomActions(string $eventName, ApprovableInterface $model, object $event): void
    {
        if ($model->getApprovalConfig('events_enabled', true)) {
            $actions = $model->getApprovalConfig("events_custom_actions.{$eventName}", []);
            foreach ($actions as $action) {
                if (is_callable($action)) {
                    $action($event);
                }
            }
        }
    }
}
