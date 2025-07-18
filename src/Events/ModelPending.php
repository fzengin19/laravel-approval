<?php

namespace LaravelApproval\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelPending
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The model that was set to pending.
     */
    public $model;

    /**
     * The approval method used.
     */
    public $approvalMethod;

    /**
     * Create a new event instance.
     */
    public function __construct($model, string $approvalMethod)
    {
        $this->model = $model;
        $this->approvalMethod = $approvalMethod;
    }

    /**
     * Get the model that was set to pending.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the approval method used.
     */
    public function getApprovalMethod(): string
    {
        return $this->approvalMethod;
    }
} 