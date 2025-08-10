<?php

namespace LaravelApproval\Tests\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LaravelApproval\Enums\ApprovalStatus;
use Tests\Models\Post;
use Tests\Models\User;
use TiMacDonald\Log\LogEntry;
use TiMacDonald\Log\LogFake;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('dispatches webhooks when webhooks are enabled', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/approval',
                'headers' => ['Authorization' => 'Bearer token'],
                'events' => ['model_approved'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/webhooks/approval' &&
               $request->header('Authorization')[0] === 'Bearer token' &&
               $request['event'] === 'model_approved';
    });
});

it('does not dispatch webhooks when webhooks are disabled', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => false,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/approval',
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);

    Http::assertNothingSent();
});

it('respects model-specific webhook configuration', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            ['url' => 'https://api.example.com/webhooks/default'],
        ],
        'approvals.models.'.Post::class => [
            'events_webhooks_enabled' => true,
            'events_webhooks_endpoints' => [
                ['url' => 'https://api.example.com/webhooks/post-approval'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/webhooks/post-approval';
    });
    Http::assertNotSent(function ($request) {
        return $request->url() === 'https://api.example.com/webhooks/default';
    });
});

it('dispatches webhooks for all events when no specific events configured', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            ['url' => 'https://api.example.com/webhooks/all-events'],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);
    $post->reject(1, 'spam', 'This is spam');

    Http::assertSentCount(4);
});

it('dispatches webhooks for specific events only', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/specific-events',
                'events' => ['model_approved', 'model_rejected'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);
    $post->reject(1, 'spam', 'This is spam');
    $post->setPending(1);

    Http::assertSent(function ($request) {
        return $request['event'] === 'model_approved';
    });
    Http::assertSent(function ($request) {
        return $request['event'] === 'model_rejected';
    });
    Http::assertNotSent(function ($request) {
        return in_array($request['event'], ['model_approving', 'model_rejecting', 'model_setting_pending']);
    });
});

it('includes approval data in webhook payload', function () {
    Http::fake();

    config([
        'approvals.default.rejection_reasons' => ['spam' => 'Spam'],
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/payload-test',
                'events' => ['model_rejected'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->reject($this->user->id, 'spam', 'This is spam content');

    Http::assertSent(function ($request) use ($post) {
        $data = $request->data();

        return $data['event'] === 'model_rejected' &&
               $data['model_class'] === Post::class &&
               $data['model_id'] === $post->id &&
               isset($data['timestamp']) &&
               $data['approval']['status'] === ApprovalStatus::REJECTED->value &&
               $data['approval']['rejection_reason'] === 'spam' &&
               $data['approval']['rejection_comment'] === 'This is spam content';
    });
});

it('handles webhook failures gracefully', function () {
    LogFake::bind();
    Http::fake([
        'https://api.example.com/*' => Http::response(null, 500),
    ]);

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/approval',
                'events' => ['model_approved'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);

    Log::assertLogged(function (LogEntry $log) {
        return $log->level === 'warning'
            && $log->message === 'Webhook failed to dispatch.'
            && $log->context['event'] === 'model_approved'
            && str_contains($log->context['exception_message'], 'HTTP request returned status code 500');
    });
});

it('dispatches webhook for model_setting_pending event', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/pending',
                'events' => ['model_setting_pending'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->setPending(1);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/webhooks/pending' &&
               $request['event'] === 'model_setting_pending';
    });
});

it('includes correct data in model_approved webhook payload', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/approved-payload',
                'events' => ['model_approved'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve($this->user->id);

    Http::assertSent(function ($request) use ($post) {
        $data = $request->data();

        return $data['event'] === 'model_approved' &&
               $data['model_class'] === Post::class &&
               $data['model_id'] === $post->id &&
               isset($data['timestamp']) &&
               $data['approval']['status'] === ApprovalStatus::APPROVED->value;
    });
});

it('includes correct data in model_setting_pending webhook payload', function () {
    Http::fake();

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/pending-payload',
                'events' => ['model_setting_pending'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->setPending($this->user->id);

    Http::assertSent(function ($request) use ($post) {
        $data = $request->data();

        return $data['event'] === 'model_setting_pending' &&
               $data['model_class'] === Post::class &&
               $data['model_id'] === $post->id &&
               isset($data['timestamp']) &&
               $data['approval']['status'] === ApprovalStatus::PENDING->value;
    });
});

it('handles webhook connection timeouts gracefully', function () {
    LogFake::bind();
    // This fake callback will throw a ConnectionException for any request.
    Http::fake(function ($request) {
        throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
    });

    config([
        'approvals.default.events_webhooks_enabled' => true,
        'approvals.default.events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/timeout',
                'events' => ['model_approved'],
            ],
        ],
    ]);

    $post = Post::factory()->create();
    $post->approve(1);

    Log::assertLogged(function (LogEntry $log) {
        return $log->level === 'warning'
            && $log->message === 'Webhook failed to dispatch.'
            && $log->context['event'] === 'model_approved'
            && $log->context['exception_class'] === \Illuminate\Http\Client\ConnectionException::class;
    });
});
