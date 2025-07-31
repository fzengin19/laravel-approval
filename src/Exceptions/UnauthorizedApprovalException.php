<?php

namespace LaravelApproval\Exceptions;

class UnauthorizedApprovalException extends ApprovalException
{
    /**
     * Create a new unauthorized approval exception instance.
     * This constructor is protected to enforce the use of static factory methods.
     *
     * @param string $action The action that was not authorized (e.g., 'approve', 'reject').
     * @param int|null $userId The ID of the user who performed the unauthorized action.
     */
    protected function __construct(string $action, ?int $userId = null)
    {
        $message = "User is not authorized to perform this action: `{$action}`";
        if ($userId) {
            $message .= " (User ID: {$userId})";
        }
        
        parent::__construct($message);
    }

    /**
     * Create an exception for an unauthorized 'approve' action.
     *
     * @param int|null $userId The ID of the user.
     * @return self
     */
    public static function cannotApprove(?int $userId = null): self
    {
        return new self('approve', $userId);
    }

    /**
     * Create an exception for an unauthorized 'reject' action.
     *
     * @param int|null $userId The ID of the user.
     * @return self
     */
    public static function cannotReject(?int $userId = null): self
    {
        return new self('reject', $userId);
    }

    /**
     * Create an exception for an unauthorized 'set pending' action.
     *
     * @param int|null $userId The ID of the user.
     * @return self
     */
    public static function cannotSetPending(?int $userId = null): self
    {
        return new self('set pending', $userId);
    }
} 