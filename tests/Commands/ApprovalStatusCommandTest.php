<?php

use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    // Create approved post
    $approvedPost = Post::create(['title' => 'Approved', 'content' => 'Content']);
    $approvedPost->approve($this->user->id);

    // Create pending post
    $pendingPost = Post::create(['title' => 'Pending', 'content' => 'Content']);
    $pendingPost->setPending($this->user->id);

    // Reddedilmiş post oluştur
    $rejectedPost = Post::create(['title' => 'Rejected', 'content' => 'Content']);
    $rejectedPost->reject($this->user->id, 'Invalid');
});

it('can show statistics for all models', function () {
    config(['approvals.models' => [Post::class => []]]);

    $this->artisan('approval:status')
        ->expectsOutputToContain('Approval Statistics for All Models')
        ->assertExitCode(0);
});

it('can show statistics for specific model', function () {
    $this->artisan('approval:status', ['--model' => Post::class])
        ->expectsOutputToContain('Total')
        ->expectsOutputToContain('Approved')
        ->expectsOutputToContain('Pending')
        ->expectsOutputToContain('Rejected')
        ->assertExitCode(0);
});

it('shows error for non-existent model', function () {
    $this->artisan('approval:status', ['--model' => 'NonExistentModel'])
        ->expectsOutputToContain("Model class 'NonExistentModel' does not exist.")
        ->assertExitCode(0);
});

it('shows message when no models are configured', function () {
    config(['approvals.models' => []]);

    $this->artisan('approval:status')
        ->expectsOutputToContain('No models configured for approval statistics.')
        ->assertExitCode(0);
});

it('shows unscoped statistics when approved-only visibility is enabled', function () {
    config([
        'approvals.models' => [Post::class => []],
        'approvals.default.show_only_approved_by_default' => true,
    ]);

    $this->artisan('approval:status')
        ->expectsTable(
            ['Model', 'Total', 'Approved', 'Pending', 'Rejected', 'Approved %'],
            [[Post::class, 3, 1, 1, 1, '33.33%']]
        )
        ->assertExitCode(0);
});
