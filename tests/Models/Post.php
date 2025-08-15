<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaravelApproval\LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;

class Post extends Model implements ApprovableInterface
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
    ];

    protected static function newFactory()
    {
        return \LaravelApproval\LaravelApproval\Tests\Database\Factories\PostFactory::new();
    }

    // ApprovableInterface implementation (minimal for testing)
    public function approve(int $causerId): self
    {
        return $this;
    }

    public function reject(int $causerId, ?string $reason = null, ?string $comment = null): self
    {
        return $this;
    }

    public function setPending(int $causerId): self
    {
        return $this;
    }

    public function isApproved(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isRejected(): bool
    {
        return false;
    }

    public function getApprovalStatus(): ?ApprovalStatus
    {
        return null;
    }

    public function getApprovalConfig(): array
    {
        return [
            'mode' => 'insert',
            'allow_custom_reasons' => true, // Allow custom reasons for testing
            'rejection_reasons' => [
                'spam' => 'Spam',
                'inappropriate' => 'Inappropriate Content',
                'other' => 'Other',
            ],
            'events_enabled' => true,
            'events_webhooks_enabled' => false,
            'events_webhooks_endpoints' => [],
        ];
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(\LaravelApproval\LaravelApproval\Models\Approval::class, 'approvable');
    }

    public function latestApproval(): MorphOne
    {
        return $this->morphOne(\LaravelApproval\LaravelApproval\Models\Approval::class, 'approvable')
            ->latestOfMany();
    }
}
