<?php

namespace LaravelApproval\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LaravelApproval\Contracts\ApprovableInterface;

class WebhookDispatcher
{
    /**
     * Dispatch webhooks for an event.
     * @param Model&ApprovableInterface $model
     */
    public function dispatchWebhooks(string $eventName, ApprovableInterface $model, object $event): void
    {
        $webhooks = $this->getModelWebhookConfig($model, 'endpoints', []);

        foreach ($webhooks as $webhook) {
            if ($this->shouldDispatchWebhook($webhook, $eventName)) {
                $this->sendWebhook($webhook, $eventName, $model, $event);
            }
        }
    }

    /**
     * Check if webhook should be dispatched for this event.
     */
    protected function shouldDispatchWebhook(array $webhook, string $eventName): bool
    {
        // If no specific events are configured, dispatch for all events
        if (empty($webhook['events'])) {
            return true;
        }

        return in_array($eventName, $webhook['events']);
    }

    /**
     * Send webhook to endpoint.
     * @param Model&ApprovableInterface $model
     */
    protected function sendWebhook(array $webhook, string $eventName, ApprovableInterface $model, object $event): void
    {
        if (!$this->getModelWebhookConfig($model, 'enabled', false)) {
            return;
        }

        $payload = $this->buildWebhookPayload($eventName, $model, $event);
        $headers = $webhook['headers'] ?? [];
        
        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($webhook['url'], $payload);

            if (! $response->successful()) {
                $response->throw();
            }
        } catch (\Throwable $e) {
            Log::warning('Webhook failed to dispatch.', [
                'url' => $webhook['url'],
                'event' => $eventName,
                'exception_message' => $e->getMessage(),
                'exception_class' => get_class($e),
            ]);
        }
    }

    /**
     * Build webhook payload.
     * @param Model&ApprovableInterface $model
     */
    protected function buildWebhookPayload(string $eventName, ApprovableInterface $model, object $event): array
    {
        $payload = [
            'event' => $eventName,
            'model_class' => get_class($model),
            'model_id' => $model->getKey(),
            'timestamp' => now()->toISOString(),
        ];

        // Add event-specific data
        if (property_exists($event, 'causedBy')) {
            $payload['caused_by'] = $event->causedBy;
        }

        if (property_exists($event, 'reason')) {
            $payload['reason'] = $event->reason;
        }

        if (property_exists($event, 'comment')) {
            $payload['comment'] = $event->comment;
        }

        if (property_exists($event, 'context')) {
            $payload['context'] = $event->context;
        }

        if (property_exists($event, 'metadata')) {
            $payload['metadata'] = $event->metadata;
        }

        if (property_exists($event, 'approval') && $event->approval) {
            $approval = $event->approval;
            $payload['approval'] = [
                'id' => $approval->id,
                'status' => $approval->status->value,
                'rejection_reason' => $approval->rejection_reason,
                'rejection_comment' => $approval->rejection_comment,
                'responded_at' => $approval->responded_at?->toISOString(),
            ];
        }

        return $payload;
    }

    /**
     * Get a specific webhook configuration value for a model, falling back to defaults.
     *
     * @param Model&ApprovableInterface $model
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    private function getModelWebhookConfig(ApprovableInterface $model, string $key, $default = null)
    {
        return $model->getApprovalConfig("events_webhooks_{$key}", $default);
    }
} 