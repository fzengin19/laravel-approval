<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;
use LaravelApproval\LaravelApproval\Tests\Models\Post;
use LaravelApproval\LaravelApproval\Tests\Models\User;

class ApprovalFactory extends Factory
{
    protected $model = Approval::class;

    public function definition(): array
    {
        return [
            'approvable_type' => Post::class,
            'approvable_id' => Post::factory(),
            'causer_type' => User::class,
            'causer_id' => User::factory(),
            'status' => $this->faker->randomElement(ApprovalStatus::cases()),
            'rejection_reason' => null,
            'rejection_comment' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::APPROVED,
            'rejection_reason' => null,
            'rejection_comment' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::PENDING,
            'rejection_reason' => null,
            'rejection_comment' => null,
        ]);
    }

    public function rejected(string $reason = 'spam', ?string $comment = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApprovalStatus::REJECTED,
            'rejection_reason' => $reason,
            'rejection_comment' => $comment ?? $this->faker->sentence(),
        ]);
    }

    public function withoutCauser(): static
    {
        return $this->state(fn (array $attributes) => [
            'causer_type' => null,
            'causer_id' => null,
        ]);
    }
}
