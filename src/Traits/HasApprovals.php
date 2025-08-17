<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;

trait HasApprovals
{
    /**
     * Get all approvals for this model.
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /**
     * Get the latest approval for this model.
     */
    public function latestApproval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->latestOfMany();
    }

    /**
     * Check if the model is approved.
     */
    public function isApproved(): bool
    {
        $latestApproval = $this->latestApproval;

        return $latestApproval && $latestApproval->status === ApprovalStatus::APPROVED;
    }

    /**
     * Check if the model is pending approval.
     */
    public function isPending(): bool
    {
        $latestApproval = $this->latestApproval;

        return $latestApproval && $latestApproval->status === ApprovalStatus::PENDING;
    }

    /**
     * Check if the model is rejected.
     */
    public function isRejected(): bool
    {
        $latestApproval = $this->latestApproval;

        return $latestApproval && $latestApproval->status === ApprovalStatus::REJECTED;
    }

    /**
     * Get the current approval status.
     */
    public function getApprovalStatus(): ?ApprovalStatus
    {
        $latestApproval = $this->latestApproval;

        return $latestApproval?->status;
    }
}
