<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelSettingPending;

class HandleModelSettingPending extends BaseApprovalListener
{
    /**
     * Handle the event.
     */
    public function handle(ModelSettingPending $event): void
    {
        $eventName = 'model_setting_pending';
        $model = $event->model;

        $this->logEvent($eventName, $model, $event);
        $this->dispatchWebhooks($eventName, $model, $event);
        $this->executeCustomActions($eventName, $model, $event);
    }
} 