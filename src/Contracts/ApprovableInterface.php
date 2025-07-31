<?php

namespace LaravelApproval\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaravelApproval\Enums\ApprovalStatus;

interface ApprovableInterface
{
    /**
     * Get all approvals for the model.
     */
    public function approvals(): MorphMany;

    /**
     * Get the latest approval for the model.
     */
    public function latestApproval(): MorphOne;

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
     * Set the model to pending status.
     */
    public function setPending(?int $userId = null, ?string $comment = null): void;

    /**
     * Approve the model.
     */
    public function approve(?int $userId = null, ?string $comment = null): void;

    /**
     * Reject the model.
     */
    public function reject(?int $userId = null, ?string $reason = null, ?string $comment = null): void;

    /**
     * Get a specific approval configuration value for the model.
     *
     * @param  mixed|null  $default
     * @return mixed
     */
    public function getApprovalConfig(string $key, $default = null);

    /**
     * Scope a query to only include approved models.
     */
    public function scopeApproved(Builder $query): Builder;

    /**
     * Scope a query to only include pending models.
     */
    public function scopePending(Builder $query): Builder;

    /**
     * Scope a query to only include rejected models.
     */
    public function scopeRejected(Builder $query): Builder;

    /**
     * Scope a query to include models with approval status.
     */
    public function scopeWithApprovalStatus(Builder $query): Builder;

    /**
     * Scope a query to include unapproved models.
     */
    public function scopeWithUnapproved(Builder $query): Builder;
}
