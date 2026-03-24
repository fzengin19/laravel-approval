<?php

use LaravelApproval\Services\ApprovalService;
use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->service = app(ApprovalService::class);
    $this->post = Post::create(['title' => 'Test', 'content' => 'Test']);
    $this->user = User::factory()->create();
});

test('ApprovalService approve, reject, setPending', function () {
    $this->service->approve($this->post, $this->user->id);
    $this->post->refresh();
    expect($this->post->isApproved())->toBeTrue();

    $this->service->reject($this->post, $this->user->id, 'spam', 'Test');
    $this->post->refresh();
    expect($this->post->isRejected())->toBeTrue();

    // setPending yeni bir model üzerinde test edilmeli çünkü reject'ten sonra geçersiz olabilir
    $newPost = Post::create(['title' => 'New Post', 'content' => 'Some content']);
    $this->service->setPending($newPost, $this->user->id);
    $newPost->refresh();
    expect($newPost->isPending())->toBeTrue();
});

test('ApprovalService statistics methods', function () {
    config(['approvals.models' => [Post::class => []]]);

    $this->service->approve($this->post, $this->user->id);
    $pendingPost = Post::create(['title' => 'Pending Post', 'content' => 'Pending']);
    $this->service->setPending($pendingPost, $this->user->id);
    $rejectedPost = Post::create(['title' => 'Rejected Post', 'content' => 'Rejected']);
    $this->service->reject($rejectedPost, $this->user->id, 'spam', 'Rejected');

    $stats = $this->service->getStatistics(Post::class);
    expect($stats)->toMatchArray([
        'total' => 3,
        'approved' => 1,
        'pending' => 1,
        'rejected' => 1,
    ]);

    $allStats = $this->service->getAllStatistics();
    expect($allStats)->toHaveKey(Post::class);

    $modelStats = $this->service->getModelStatistics($this->post);
    expect($modelStats)->toMatchArray($stats);

    $approvalPct = $this->service->getApprovalPercentage(Post::class);
    expect($approvalPct)->toBe(33.33);

    $rejectionPct = $this->service->getRejectionPercentage(Post::class);
    expect($rejectionPct)->toBe(33.33);

    $pendingPct = $this->service->getPendingPercentage(Post::class);
    expect($pendingPct)->toBe(33.33);

    $detailed = $this->service->getDetailedStatistics(Post::class);
    expect($detailed)->toHaveKeys(['latest_approvals', 'rejection_reasons']);

    $dateStats = $this->service->getStatisticsForDateRange(Post::class, now()->subDay()->toDateString(), now()->toDateString());
    expect($dateStats)->toHaveKeys(['total', 'approved', 'pending', 'rejected', 'approved_percentage', 'pending_percentage', 'rejected_percentage', 'date_range']);
});
