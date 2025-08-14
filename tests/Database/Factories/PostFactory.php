<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelApproval\LaravelApproval\Tests\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
        ];
    }
}
