<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LaravelApproval\LaravelApproval\Database\Factories\ApprovalFactory;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'causer_type',
        'causer_id',
        'status',
        'rejection_reason',
        'rejection_comment',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): ApprovalFactory
    {
        return ApprovalFactory::new();
    }
}
