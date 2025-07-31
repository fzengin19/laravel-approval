<?php

namespace LaravelApproval\Traits;

use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasApprovals
{
    /**
     * Get all the approvals for the model.
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /**
     * Get the latest approval for the model.
     */
    public function latestApproval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->latestOfMany();
    }

    /**
     * Get the current approval status of the model.
     */
    public function getApprovalStatus(): ?ApprovalStatus
    {
        // latestApproval relationship already returns a model with status casted to Enum
        if ($this->latestApproval) {
            return $this->latestApproval->status;
        }

        $defaultStatus = $this->getApprovalConfig('default_status_for_unaudited');

        return ApprovalStatus::tryFrom($defaultStatus ?? '');
    }

    /**
     * Check if the model is approved.
     */
    public function isApproved(): bool
    {
        return $this->getApprovalStatus() === ApprovalStatus::APPROVED;
    }

    /**
     * Check if the model is pending.
     */
    public function isPending(): bool
    {
        return $this->getApprovalStatus() === ApprovalStatus::PENDING;
    }

    /**
     * Check if the model is rejected.
     */
    public function isRejected(): bool
    {
        return $this->getApprovalStatus() === ApprovalStatus::REJECTED;
    }
} 