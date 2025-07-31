<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\Models\Post;

beforeEach(function () {
    $this->logPath = storage_path('logs/test.log');
    if (File::exists($this->logPath)) {
        File::delete($this->logPath);
    }
    Config::set('logging.channels.test_channel', [
        'driver' => 'single',
        'path' => $this->logPath,
    ]);
    Config::set('approvals.default.events_logging_channel', 'test_channel');
});

afterEach(function () {
    if (File::exists($this->logPath)) {
        File::delete($this->logPath);
    }
});

test('it logs events when logging is enabled', function () {
    Config::set('approvals.default.events_logging', true);

    $post = Post::create(['title' => 'Test', 'content' => 'Test']);
    $post->approve(1);

    $logContent = File::get($this->logPath);
    expect($logContent)->toContain('Approval event: model_approving');
    expect($logContent)->toContain('Approval event: model_approved');
});

test('it does not log events when logging is disabled', function () {
    Config::set('approvals.default.events_logging', false);

    $post = Post::create(['title' => 'Test', 'content' => 'Test']);
    $post->approve(1);

    expect(File::exists($this->logPath))->toBeFalse();
});

test('it executes custom actions when configured', function () {
    $customActionWasCalled = false;

    Config::set('approvals.default.events_custom_actions', [
        'model_approved' => [
            function ($event) use (&$customActionWasCalled) {
                $customActionWasCalled = true;
            },
        ],
    ]);

    $post = Post::create(['title' => 'Test', 'content' => 'Test']);
    $post->approve(1);

    expect($customActionWasCalled)->toBeTrue();
});

test('it does not execute custom actions when not configured', function () {
    $customActionWasCalled = false;

    Config::set('approvals.default.events_custom_actions', []);

    $post = Post::create(['title' => 'Test', 'content' => 'Test']);
    $post->approve(1);

    expect($customActionWasCalled)->toBeFalse();
});
