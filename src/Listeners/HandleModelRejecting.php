<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelRejecting;

class HandleModelRejecting extends BaseApprovalListener
{
    /**
     * Handle the event.
     */
    public function handle(ModelRejecting $event): void
    {
        $eventName = 'model_rejecting';
        $model = $event->model;

        $this->logEvent($eventName, $model, $event);
        $this->dispatchWebhooks($eventName, $model, $event);
        $this->executeCustomActions($eventName, $model, $event);
    }
} 