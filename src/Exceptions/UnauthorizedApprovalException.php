<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Exceptions;

class UnauthorizedApprovalException extends ApprovalException
{
    public function __construct(string $message = '', int $code = 2001, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function forUser(int|string $userId, string $action): self
    {
        $message = "User with ID {$userId} is not authorized to perform '{$action}' action.";

        return new self($message, 2001);
    }

    public static function forMissingCauser(): self
    {
        return new self('Approval action requires a causer (user who performs the action).', 2002);
    }

    public static function forInvalidCauser(mixed $causerId): self
    {
        $message = "Invalid causer ID '{$causerId}'. Causer must be a valid user ID.";

        return new self($message, 2003);
    }

    public static function forSelfApproval(int|string $userId): self
    {
        $message = "User with ID {$userId} cannot approve their own content.";

        return new self($message, 2004);
    }

    public static function forRole(string $role, string $requiredRole): self
    {
        $message = "Role '{$role}' is not authorized. Required role: '{$requiredRole}'.";

        return new self($message, 2005);
    }
}
