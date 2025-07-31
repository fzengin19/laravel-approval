<?php

namespace LaravelApproval\Contracts;

use LaravelApproval\Contracts\ApprovableInterface;

interface ApprovalValidatorInterface
{
    /**
     * Validate whether the approval action should be allowed.
     * This is a general validation hook before checking specific permissions.
     *
     * @param ApprovableInterface $model The model being approved.
     * @param int|null $userId The ID of the user performing the action.
     * @return bool `true` to allow the process to continue, `false` to block.
     */
    public function validateApproval(ApprovableInterface $model, ?int $userId = null): bool;

    /**
     * Validate whether the rejection action should be allowed.
     * This is a general validation hook before checking specific permissions.
     *
     * @param ApprovableInterface $model The model being rejected.
     * @param int|null $userId The ID of the user performing the action.
     * @param string|null $reason The reason for rejection.
     * @return bool `true` to allow the process to continue, `false` to block.
     */
    public function validateRejection(ApprovableInterface $model, ?int $userId = null, ?string $reason = null): bool;

    /**
     * Validate whether setting the model to 'pending' should be allowed.
     * This is a general validation hook before checking specific permissions.
     *
     * @param ApprovableInterface $model The model being set to pending.
     * @param int|null $userId The ID of the user performing the action.
     * @return bool `true` to allow the process to continue, `false` to block.
     */
    public function validatePending(ApprovableInterface $model, ?int $userId = null): bool;

    /**
     * Validate if the provided rejection reason is valid based on the allowed reasons.
     *
     * @param string $reason The rejection reason to validate.
     * @param array $allowedReasons A list of explicitly allowed reasons from config.
     * @return bool `true` if the reason is valid, `false` otherwise.
     */
    public function validateRejectionReason(string $reason, array $allowedReasons = []): bool;

    /**
     * Check if the specific user has permission to approve the model.
     *
     * @param ApprovableInterface $model The model to be approved.
     * @param int|null $userId The ID of the user to check.
     * @return bool `true` if the user is authorized, `false` otherwise.
     */
    public function canApprove(ApprovableInterface $model, ?int $userId = null): bool;

    /**
     * Check if the specific user has permission to reject the model.
     *
     * @param ApprovableInterface $model The model to be rejected.
     * @param int|null $userId The ID of the user to check.
     * @return bool `true` if the user is authorized, `false` otherwise.
     */
    public function canReject(ApprovableInterface $model, ?int $userId = null): bool;

    /**
     * Check if the specific user has permission to set the model to 'pending'.
     *
     * @param ApprovableInterface $model The model to be set to pending.
     * @param int|null $userId The ID of the user to check.
     * @return bool `true` if the user is authorized, `false` otherwise.
     */
    public function canSetPending(ApprovableInterface $model, ?int $userId = null): bool;
} 