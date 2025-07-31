<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelPending;

class HandleModelPending extends BaseApprovalListener
{
    /**
     * Handle the event.
     */
    public function handle(ModelPending $event): void
    {
        $eventName = 'model_pending';
        $model = $event->model;

        $this->logEvent($eventName, $model, $event);
        $this->dispatchWebhooks($eventName, $model, $event);
        $this->executeCustomActions($eventName, $model, $event);
    }
} 