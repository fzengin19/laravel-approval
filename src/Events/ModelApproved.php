<?php

namespace LaravelApproval\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Models\Approval;

class ModelApproved
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  ApprovableInterface  $model  The model that was approved.
     * @param  Approval  $approval  The approval record.
     * @param  int|null  $causedBy  The ID of the user who triggered the event.
     * @param  string|null  $comment  An optional comment.
     * @param  array  $context  Additional context data.
     * @param  array  $metadata  Additional metadata.
     */
    public function __construct(
        public readonly ApprovableInterface $model,
        public readonly Approval $approval,
        public readonly ?int $causedBy = null,
        public readonly ?string $comment = null,
        public readonly array $context = [],
        public readonly array $metadata = []
    ) {}
}
