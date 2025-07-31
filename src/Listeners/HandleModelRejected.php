<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelRejected;

class HandleModelRejected extends BaseApprovalListener
{
    /**
     * Handle the event.
     */
    public function handle(ModelRejected $event): void
    {
        $eventName = 'model_rejected';
        $model = $event->model;

        $this->logEvent($eventName, $model, $event);
        $this->dispatchWebhooks($eventName, $model, $event);
        $this->executeCustomActions($eventName, $model, $event);
    }
}
