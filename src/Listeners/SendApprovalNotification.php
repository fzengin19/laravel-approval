<?php

namespace LaravelApproval\Listeners;

use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof ModelApproved) {
            $this->handleModelApproved($event);
        } elseif ($event instanceof ModelRejected) {
            $this->handleModelRejected($event);
        } elseif ($event instanceof ModelPending) {
            $this->handleModelPending($event);
        }
    }

    /**
     * Handle model approved event.
     */
    protected function handleModelApproved(ModelApproved $event): void
    {
        $model = $event->getModel();
        $approvedBy = $event->getApprovedBy();
        $approvedAt = $event->getApprovedAt();
        $method = $event->getApprovalMethod();

        Log::info('Model approved', [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
            'method' => $method,
        ]);

        // Here you can add notification logic
        // For example, send email notifications, database notifications, etc.
    }

    /**
     * Handle model rejected event.
     */
    protected function handleModelRejected(ModelRejected $event): void
    {
        $model = $event->getModel();
        $rejectedBy = $event->getRejectedBy();
        $reason = $event->getRejectionReason();
        $method = $event->getApprovalMethod();

        Log::info('Model rejected', [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'rejected_by' => $rejectedBy,
            'reason' => $reason,
            'method' => $method,
        ]);

        // Here you can add notification logic
        // For example, send email notifications, database notifications, etc.
    }

    /**
     * Handle model pending event.
     */
    protected function handleModelPending(ModelPending $event): void
    {
        $model = $event->getModel();
        $method = $event->getApprovalMethod();

        Log::info('Model set to pending', [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'method' => $method,
        ]);

        // Here you can add notification logic
        // For example, send email notifications, database notifications, etc.
    }
} 