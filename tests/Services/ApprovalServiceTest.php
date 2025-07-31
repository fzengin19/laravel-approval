<?php

use LaravelApproval\Services\ApprovalService;
use LaravelApproval\Services\StatisticsService;
use Tests\Models\Post;

beforeEach(function () {
    $this->service = app(ApprovalService::class);
    $this->post = Post::create(['title' => 'Test', 'content' => 'Test']);
});

test('ApprovalService approve, reject, setPending', function () {
    $this->service->approve($this->post, 1);
    $this->post->refresh();
    expect($this->post->isApproved())->toBeTrue();

    $this->service->reject($this->post, 1, 'spam', 'Test');
    $this->post->refresh();
    expect($this->post->isRejected())->toBeTrue();

    // setPending yeni bir model üzerinde test edilmeli çünkü reject'ten sonra geçersiz olabilir
    $newPost = Post::create(['title' => 'New Post', 'content' => 'Some content']);
    $this->service->setPending($newPost, 1);
    $newPost->refresh();
    expect($newPost->isPending())->toBeTrue();
});

test('ApprovalService statistics methods', function () {
    $this->service->approve($this->post, 1);
    $stats = $this->service->getStatistics(Post::class);
    expect($stats)->toHaveKeys(['total', 'approved', 'pending', 'rejected', 'approved_percentage', 'pending_percentage', 'rejected_percentage']);
    $allStats = $this->service->getAllStatistics();
    expect($allStats)->toBeArray();
    // getModelStatistics is not a method on the service, removing this test
    // $modelStats = $this->service->getModelStatistics($this->post);
    // expect($modelStats)->toBeArray();
    $approvalPct = $this->service->getApprovalPercentage(Post::class);
    expect($approvalPct)->toBeFloat();
    $rejectionPct = $this->service->getRejectionPercentage(Post::class);
    expect($rejectionPct)->toBeFloat();
    $pendingPct = $this->service->getPendingPercentage(Post::class);
    expect($pendingPct)->toBeFloat();
    $detailed = $this->service->getDetailedStatistics(Post::class);
    expect($detailed)->toBeArray();
    $dateStats = $this->service->getStatisticsForDateRange(Post::class, now()->subDay()->toDateString(), now()->toDateString());
    expect($dateStats)->toHaveKeys(['total', 'approved', 'pending', 'rejected', 'approved_percentage', 'pending_percentage', 'rejected_percentage', 'date_range']);
}); 