<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelApproved;

class HandleModelApproved extends BaseApprovalListener
{
    /**
     * Handle the event.
     */
    public function handle(ModelApproved $event): void
    {
        $eventName = 'model_approved';
        $model = $event->model;

        $this->logEvent($eventName, $model, $event);
        $this->dispatchWebhooks($eventName, $model, $event);
        $this->executeCustomActions($eventName, $model, $event);
    }
} 