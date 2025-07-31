<?php

namespace LaravelApproval\Traits;

use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Scopes\ApprovableScope;
use Illuminate\Database\Eloquent\Builder;

trait ApprovalScopes
{
    /**
     * Scope a query to only include approved models.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $this->scopeWhereStatus($query, ApprovalStatus::APPROVED);
    }

    /**
     * Scope a query to only include pending models.
     */
    public function scopePending(Builder $query): Builder
    {
        return $this->scopeWhereStatus($query, ApprovalStatus::PENDING);
    }

    /**
     * Scope a query to only include rejected models.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $this->scopeWhereStatus($query, ApprovalStatus::REJECTED);
    }

    /**
     * A generic scope to filter models by a specific approval status.
     */
    public function scopeWhereStatus(Builder $query, ApprovalStatus $status): Builder
    {
        $unauditedStatus = $this->getApprovalConfig('default_status_for_unaudited');

        return $query->where(function (Builder $q) use ($status, $unauditedStatus) {
            $q->whereHas('latestApproval', function (Builder $subQuery) use ($status) {
                $subQuery->where('status', $status);
            });

            if ($unauditedStatus === $status->value) {
                $q->orWhereDoesntHave('approvals');
            }
        });
    }

    /**
     * Scope a query to include models with approval status.
     */
    public function scopeWithApprovalStatus(Builder $query): Builder
    {
        return $query->with('latestApproval');
    }

    /**
     * Scope a query to include unapproved models.
     */
    public function scopeWithUnapproved(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ApprovableScope::class);
    }
} 