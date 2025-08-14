<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Enums;

enum ApprovalStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
