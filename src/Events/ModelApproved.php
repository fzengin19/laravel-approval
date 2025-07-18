<?php

namespace LaravelApproval\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The model that was approved.
     */
    public $model;

    /**
     * The user who approved the model.
     */
    public $approvedBy;

    /**
     * The approval date.
     */
    public $approvedAt;

    /**
     * The approval method used.
     */
    public $approvalMethod;

    /**
     * Create a new event instance.
     */
    public function __construct($model, ?int $approvedBy, $approvedAt, string $approvalMethod)
    {
        $this->model = $model;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = $approvedAt;
        $this->approvalMethod = $approvalMethod;
    }

    /**
     * Get the model that was approved.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the user who approved the model.
     */
    public function getApprovedBy(): ?int
    {
        return $this->approvedBy;
    }

    /**
     * Get the approval date.
     */
    public function getApprovedAt()
    {
        return $this->approvedAt;
    }

    /**
     * Get the approval method used.
     */
    public function getApprovalMethod(): string
    {
        return $this->approvalMethod;
    }
} 