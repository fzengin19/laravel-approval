<?php

namespace LaravelApproval\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;
use Tests\Models\User;

class ApprovalFactory extends Factory
{
    protected $model = Approval::class;

    public function definition(): array
    {
        return [
            'approvable_type' => Post::class,
            'approvable_id' => Post::factory(),
            'status' => $this->faker->randomElement(ApprovalStatus::cases()),
            'rejection_reason' => null,
            'rejection_comment' => null,
            'caused_by_type' => User::class,
            'caused_by_id' => User::factory(),
            'responded_at' => $this->faker->optional()->dateTime(),
        ];
    }
} 