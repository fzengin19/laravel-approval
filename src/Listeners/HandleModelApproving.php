<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelApproving;

class HandleModelApproving extends BaseApprovalListener
{
    /**
     * Handle the event.
     */
    public function handle(ModelApproving $event): void
    {
        $eventName = 'model_approving';
        $model = $event->model;

        $this->logEvent($eventName, $model, $event);
        $this->dispatchWebhooks($eventName, $model, $event);
        $this->executeCustomActions($eventName, $model, $event);
    }
}
