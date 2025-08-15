<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;

interface ApprovableInterface
{
    /**
     * Get the primary key value.
     */
    public function getKey();

    /**
     * Approve the model.
     */
    public function approve(int $causerId): self;

    /**
     * Reject the model with a reason and optional comment.
     */
    public function reject(int $causerId, ?string $reason = null, ?string $comment = null): self;

    /**
     * Set the model to pending status.
     */
    public function setPending(int $causerId): self;

    /**
     * Check if the model is approved.
     */
    public function isApproved(): bool;

    /**
     * Check if the model is pending.
     */
    public function isPending(): bool;

    /**
     * Check if the model is rejected.
     */
    public function isRejected(): bool;

    /**
     * Get the current approval status.
     */
    public function getApprovalStatus(): ?ApprovalStatus;

    /**
     * Get the approval configuration for this model.
     */
    public function getApprovalConfig();

    /**
     * Get all approval records for this model.
     */
    public function approvals(): MorphMany;

    /**
     * Get the latest approval record for this model.
     */
    public function latestApproval(): MorphOne;
}
