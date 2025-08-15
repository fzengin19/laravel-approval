<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Contracts;

use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;

interface ApprovalValidatorInterface
{
    /**
     * Validate approval operation.
     */
    public function validateApproval(ApprovableInterface $model, ?int $causerId = null): void;

    /**
     * Validate rejection operation.
     */
    public function validateRejection(ApprovableInterface $model, ?int $causerId = null, ?string $reason = null, ?string $comment = null): void;

    /**
     * Validate pending operation.
     */
    public function validatePending(ApprovableInterface $model, ?int $causerId = null): void;

    /**
     * Validate status transition.
     */
    public function validateStatusTransition(ApprovableInterface $model, ApprovalStatus $newStatus): void;

    /**
     * Validate model configuration.
     */
    public function validateModelConfiguration(ApprovableInterface $model): void;
}
