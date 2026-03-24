<?php

use LaravelApproval\Services\StatisticsService;
use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->service = app(StatisticsService::class);
    $this->post = Post::create(['title' => 'Test', 'content' => 'Test']);
    $this->user = User::factory()->create();
});

test('StatisticsService getStatistics and getAllStatistics', function () {
    config(['approvals.models' => [Post::class => []]]);

    $this->post->approve($this->user->id);

    $stats = $this->service->getStatistics(Post::class);
    $allStats = $this->service->getAllStatistics();

    expect($stats)->toMatchArray([
        'total' => 1,
        'approved' => 1,
        'pending' => 0,
        'rejected' => 0,
    ]);
    expect($allStats)->toHaveKey(Post::class);
    expect($allStats[Post::class])->toMatchArray($stats);
});

test('StatisticsService getModelStatistics', function () {
    $this->post->approve($this->user->id);

    $modelStats = $this->service->getModelStatistics($this->post);

    expect($modelStats)->toMatchArray($this->service->getStatistics(Post::class));
});

test('StatisticsService getApprovalPercentage, getRejectionPercentage, getPendingPercentage', function () {
    Post::factory()->create()->approve($this->user->id);
    Post::factory()->create()->reject($this->user->id, 'spam', 'Rejected');
    Post::factory()->create()->setPending($this->user->id);

    expect($this->service->getApprovalPercentage(Post::class))->toBe(25.0);
    expect($this->service->getRejectionPercentage(Post::class))->toBe(25.0);
    expect($this->service->getPendingPercentage(Post::class))->toBe(25.0);
});

test('StatisticsService getDetailedStatistics', function () {
    $this->post->reject($this->user->id, 'spam', 'Rejected');

    $detailedStats = $this->service->getDetailedStatistics(Post::class);

    expect($detailedStats)->toHaveKeys([
        'total',
        'approved',
        'pending',
        'rejected',
        'approved_percentage',
        'pending_percentage',
        'rejected_percentage',
        'latest_approvals',
        'rejection_reasons',
    ]);
    expect($detailedStats['latest_approvals'])->toHaveCount(1);
    expect($detailedStats['rejection_reasons'])->toHaveCount(1);
});

test('StatisticsService getStatisticsForDateRange', function () {
    $this->post->approve($this->user->id);

    $dateStats = $this->service->getStatisticsForDateRange(
        Post::class,
        now()->subDay()->toDateString(),
        now()->toDateString()
    );

    expect($dateStats)->toMatchArray([
        'total' => 1,
        'approved' => 1,
        'pending' => 0,
        'rejected' => 0,
    ]);
    expect($dateStats['date_range'])->toBe([
        'start' => now()->subDay()->toDateString(),
        'end' => now()->toDateString(),
    ]);
});

test('it handles division by zero gracefully when calculating percentages with no approvals', function () {
    Post::query()->delete();

    $percentage = $this->service->getApprovalPercentage(Post::class);
    $this->assertEquals(0.0, $percentage);

    $percentage = $this->service->getRejectionPercentage(Post::class);
    $this->assertEquals(0.0, $percentage);

    $percentage = $this->service->getPendingPercentage(Post::class);
    $this->assertEquals(0.0, $percentage);
});

test('it returns empty statistics for a date range with no approvals', function () {
    Post::query()->delete();

    $stats = $this->service->getStatisticsForDateRange(Post::class, '2023-01-01', '2023-01-31');

    $this->assertEquals(0, $stats['total']);
    $this->assertSame(0, $stats['approved']);
    $this->assertSame(0, $stats['pending']);
    $this->assertSame(0, $stats['rejected']);
});

test('it throws exception for invalid date range in getStatisticsForDateRange', function ($startDate, $endDate) {
    $this->expectException(InvalidArgumentException::class);
    $this->service->getStatisticsForDateRange(Post::class, $startDate, $endDate);
})->with([
    ['invalid-date', '2023-01-31'],
    ['2023-01-01', 'invalid-date'],
    ['', '2023-01-31'],
    ['2023-01-01', ''],
]);

test('it returns empty detailed statistics for a model with no approvals', function () {
    Post::factory()->create();

    $stats = $this->service->getDetailedStatistics(Post::class);

    $this->assertIsArray($stats);
    $this->assertEmpty($stats);
});

test('StatisticsService getStatistics ignores approved-only visibility scope', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $approvedPost = Post::factory()->create();
    $approvedPost->approve($this->user->id);

    $pendingPost = Post::factory()->create();
    $pendingPost->setPending($this->user->id);

    $rejectedPost = Post::factory()->create();
    $rejectedPost->reject($this->user->id, 'spam', 'Rejected');

    $stats = $this->service->getStatistics(Post::class);

    expect($stats)->toMatchArray([
        'total' => 4,
        'approved' => 1,
        'pending' => 1,
        'rejected' => 1,
    ]);
});
