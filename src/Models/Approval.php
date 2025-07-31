<?php

namespace LaravelApproval\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LaravelApproval\Database\Factories\ApprovalFactory;
use LaravelApproval\Enums\ApprovalStatus;

/**
 * @property ApprovalStatus $status
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property-read Model $approvable
 * @property-read Model|null $causer
 */
class Approval extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return ApprovalFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'rejection_reason',
        'rejection_comment',
        'responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string|class-string>
     */
    protected $casts = [
        'responded_at' => 'datetime',
        'status' => ApprovalStatus::class,
    ];

    /**
     * Get the parent approvable model (the model that requires approval).
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the model that caused the approval action (e.g., the user who approved).
     */
    public function causer(): MorphTo
    {
        return $this->morphTo('caused_by');
    }

    /**
     * Scope a query to only include pending approvals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopePending($query)
    {
        return $query->where('status', ApprovalStatus::PENDING);
    }

    /**
     * Scope a query to only include approved approvals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeApproved($query)
    {
        return $query->where('status', ApprovalStatus::APPROVED);
    }

    /**
     * Scope a query to only include rejected approvals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     * @return \Illuminate\Database\Eloquent\Builder<self>
     */
    public function scopeRejected($query)
    {
        return $query->where('status', ApprovalStatus::REJECTED);
    }
}
