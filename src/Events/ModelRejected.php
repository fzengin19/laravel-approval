<?php

namespace LaravelApproval\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The model that was rejected.
     */
    public $model;

    /**
     * The user who rejected the model.
     */
    public $rejectedBy;

    /**
     * The rejection reason.
     */
    public $rejectionReason;

    /**
     * The approval method used.
     */
    public $approvalMethod;

    /**
     * Create a new event instance.
     */
    public function __construct($model, ?int $rejectedBy, string $rejectionReason, string $approvalMethod)
    {
        $this->model = $model;
        $this->rejectedBy = $rejectedBy;
        $this->rejectionReason = $rejectionReason;
        $this->approvalMethod = $approvalMethod;
    }

    /**
     * Get the model that was rejected.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the user who rejected the model.
     */
    public function getRejectedBy(): ?int
    {
        return $this->rejectedBy;
    }

    /**
     * Get the rejection reason.
     */
    public function getRejectionReason(): string
    {
        return $this->rejectionReason;
    }

    /**
     * Get the approval method used.
     */
    public function getApprovalMethod(): string
    {
        return $this->approvalMethod;
    }
} 