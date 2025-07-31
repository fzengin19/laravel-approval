<?php

use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;
use Tests\Models\Post;
use Tests\Models\User;

beforeEach(function () {
    $this->post = Post::factory()->create();
    $this->user = User::factory()->create();
});

it('can access approvals relationship', function () {
    Approval::factory()->count(3)->create([
        'approvable_type' => $this->post->getMorphClass(),
        'approvable_id' => $this->post->id,
    ]);

    expect($this->post->approvals()->count())->toBe(3);
});

it('can access latest approval relationship', function () {
    Approval::factory()->create([
        'approvable_id' => $this->post->id,
        'approvable_type' => $this->post->getMorphClass(),
        'created_at' => now()->subDay(),
    ]);

    $latestApproval = Approval::factory()->create([
        'approvable_id' => $this->post->id,
        'approvable_type' => $this->post->getMorphClass(),
        'status' => ApprovalStatus::REJECTED,
        'created_at' => now(),
    ]);

    expect($this->post->latestApproval)->toBeInstanceOf(Approval::class);
    expect($this->post->latestApproval->id)->toBe($latestApproval->id);
    expect($this->post->latestApproval->status)->toBe(ApprovalStatus::REJECTED);
});
