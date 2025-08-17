<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelApproval\LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\LaravelApproval\Traits\HasApprovals;

class Post extends Model implements ApprovableInterface
{
    use HasApprovals, HasFactory;

    protected $fillable = [
        'title',
        'content',
    ];

    protected static function newFactory()
    {
        return \LaravelApproval\LaravelApproval\Tests\Database\Factories\PostFactory::new();
    }

    // ApprovableInterface implementation (minimal for testing)
    public function getKey()
    {
        return $this->getKeyName() ? $this->{$this->getKeyName()} : null;
    }

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

    // Status methods come from HasApprovals trait

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

    // Relationship methods come from HasApprovals trait
}
