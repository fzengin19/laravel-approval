<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelApproval\LaravelApproval\Tests\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
