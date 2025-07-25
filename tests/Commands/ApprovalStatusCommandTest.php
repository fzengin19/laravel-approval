<?php

use Tests\Models\Post;

// Post modeli zaten Approvable trait'ini kullanıyor
class CommandTestPost extends Post
{
    protected $table = 'posts';
}

beforeEach(function () {
    // Create approved post
    $approvedPost = CommandTestPost::create(['title' => 'Approved', 'content' => 'Content']);
    $approvedPost->approve(1);

    // Create pending post
    $pendingPost = CommandTestPost::create(['title' => 'Pending', 'content' => 'Content']);
    $pendingPost->setPending(1);

    // Reddedilmiş post oluştur
    $rejectedPost = CommandTestPost::create(['title' => 'Rejected', 'content' => 'Content']);
    $rejectedPost->reject(1, 'Invalid');
});

it('can show statistics for all models', function () {
    config(['approvals.models' => [CommandTestPost::class => []]]);

    $this->artisan('approval:status')
        ->expectsOutputToContain('Approval Statistics for All Models')
        ->assertExitCode(0);
});

it('can show statistics for specific model', function () {
    $this->artisan('approval:status', ['--model' => CommandTestPost::class])
        ->expectsOutputToContain('Approval Statistics for '.CommandTestPost::class)
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
