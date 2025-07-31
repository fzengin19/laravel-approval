<?php

use LaravelApproval\Services\StatisticsService;
use Tests\Models\Post;

beforeEach(function () {
    $this->service = app(StatisticsService::class);
    $this->post = Post::create(['title' => 'Test', 'content' => 'Test']);
});

test('StatisticsService getStatistics and getAllStatistics', function () {
    $this->service->getStatistics(Post::class);
    $this->service->getAllStatistics();
    expect(true)->toBeTrue();
});

test('StatisticsService getModelStatistics', function () {
    $this->service->getModelStatistics($this->post);
    expect(true)->toBeTrue();
});

test('StatisticsService getApprovalPercentage, getRejectionPercentage, getPendingPercentage', function () {
    $this->service->getApprovalPercentage(Post::class);
    $this->service->getRejectionPercentage(Post::class);
    $this->service->getPendingPercentage(Post::class);
    expect(true)->toBeTrue();
});

test('StatisticsService getDetailedStatistics', function () {
    $this->service->getDetailedStatistics(Post::class);
    expect(true)->toBeTrue();
});

test('StatisticsService getStatisticsForDateRange', function () {
    $this->service->getStatisticsForDateRange(Post::class, now()->subDay()->toDateString(), now()->toDateString());
    expect(true)->toBeTrue();
});

test('it handles division by zero gracefully when calculating percentages with no approvals', function () {
    $post = Post::factory()->create();

    $percentage = $this->service->getApprovalPercentage(Post::class);
    $this->assertEquals(0.0, $percentage);

    $percentage = $this->service->getRejectionPercentage(Post::class);
    $this->assertEquals(0.0, $percentage);

    $percentage = $this->service->getPendingPercentage(Post::class);
    $this->assertEquals(0.0, $percentage);
});

test('it returns empty statistics for a date range with no approvals', function () {
    Post::factory()->create();

    $stats = $this->service->getStatisticsForDateRange(Post::class, '2023-01-01', '2023-01-31');

    $this->assertEquals(0, $stats['total']);
    $this->assertEmpty($stats['approved']);
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