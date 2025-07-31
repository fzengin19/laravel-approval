<?php

namespace LaravelApproval\Exceptions;

use LaravelApproval\Enums\ApprovalStatus;

class InvalidApprovalStatusException extends ApprovalException
{
    /**
     * Create an exception for an invalid status transition.
     *
     * @param string $fromStatus The original status.
     * @param string $toStatus The target status.
     * @return self
     */
    public static function invalidTransition(string $fromStatus, string $toStatus): self
    {
        return new self("Cannot transition from {$fromStatus} to {$toStatus}");
    }

    /**
     * Create an exception for a status that is not recognized by the system.
     * Overrides the parent method to provide a more specific message and context.
     *
     * @param string $status The unknown status.
     * @return self
     */
    public static function invalidStatus(string $status): self
    {
        $allowed = implode(', ', array_map(fn($case) => $case->value, ApprovalStatus::cases()));
        $message = "Unknown approval status: `{$status}`. Allowed statuses are: {$allowed}.";

        return new self($message);
    }
} 