<?php

namespace LaravelApproval\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LaravelApproval\Models\Approval;

class ModelPending
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public $model,
        public Approval $approval
    ) {}
}
