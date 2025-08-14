<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Contracts;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;

interface ApprovalValidatorInterface
{
    /**
     * Validate approval data array.
     */
    public function validateApprovalData(array $data): bool;

    /**
     * Validate approval status.
     */
    public function validateStatus(ApprovalStatus|string $status): bool;

    /**
     * Validate causer ID.
     */
    public function validateCauser(int|string|null $causerId): bool;

    /**
     * Validate model for approval operations.
     */
    public function validateModel(Model $model): bool;

    /**
     * Validate rejection reason.
     */
    public function validateRejectionReason(string $reason, string $modelClass): bool;

    /**
     * Check if user can approve the model.
     */
    public function canApprove(Model $model, int $causerId): bool;

    /**
     * Check if user can reject the model.
     */
    public function canReject(Model $model, int $causerId): bool;

    /**
     * Check if user can set the model to pending.
     */
    public function canSetPending(Model $model, int $causerId): bool;
}
