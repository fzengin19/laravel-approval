<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Exceptions;

class InvalidApprovalStatusException extends ApprovalException
{
    public function __construct(string $message = '', int $code = 1001, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function forInvalidStatus(string $invalidStatus): self
    {
        $message = "Invalid approval status '{$invalidStatus}'. Valid statuses are: pending, approved, rejected.";

        return new self($message, 1001);
    }

    public static function forEmptyStatus(): self
    {
        return new self('Approval status cannot be empty.', 1002);
    }

    public static function forNullStatus(): self
    {
        return new self('Approval status cannot be null.', 1003);
    }
}
