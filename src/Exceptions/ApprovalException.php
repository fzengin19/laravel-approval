<?php

namespace LaravelApproval\Exceptions;

use Exception;

class ApprovalException extends Exception
{
    /**
     * Create an exception for an invalid approval status.
     *
     * @param string $status The invalid status.
     * @return self
     */
    public static function invalidStatus(string $status): self
    {
        return new self("Invalid approval status: {$status}");
    }

    /**
     * Create an exception for a model that cannot be found.
     *
     * @param string $modelClass The class name of the model.
     * @return self
     */
    public static function modelNotFound(string $modelClass): self
    {
        return new self("Model not found: {$modelClass}");
    }

    /**
     * Create an exception for an approval record that cannot be found.
     *
     * @param int $approvalId The ID of the approval.
     * @return self
     */
    public static function approvalNotFound(int $approvalId): self
    {
        return new self("Approval not found: {$approvalId}");
    }

    /**
     * Create an exception for an invalid rejection reason.
     *
     * @param string $reason The invalid reason.
     * @return self
     */
    public static function invalidRejectionReason(string $reason): self
    {
        return new self("Invalid rejection reason: {$reason}");
    }

    /**
     * Create an exception for a configuration error.
     *
     * @param string $key The configuration key that is invalid.
     * @return self
     */
    public static function configurationError(string $key): self
    {
        return new self("Configuration error for key: {$key}");
    }
} 