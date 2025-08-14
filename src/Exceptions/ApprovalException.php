<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Exceptions;

use Exception;

class ApprovalException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
