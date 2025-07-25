<?php

use Illuminate\Support\Facades\Auth;
use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\HasApprovals;
use Workbench\App\Models\Post;

// Test için Post modelini HasApprovals trait'i ile genişlet
class ApproveRejectTestPost extends Post
{
    use HasApprovals;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = ApproveRejectTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('can approve in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->approve(1);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('approved');
    expect($approval->caused_by)->toBe(1);
    expect($approval->responded_at)->not->toBeNull();

    // İkinci kez çağrıldığında yeni kayıt oluşturmalı
    $secondApproval = $this->post->approve(2);
    expect($secondApproval->id)->not->toBe($approval->id);
    expect($this->post->approvals()->count())->toBe(2);
});

it('can approve in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $approval = $this->post->approve(1);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('approved');

    // İkinci kez çağrıldığında mevcut kaydı güncellemeli
    $secondApproval = $this->post->approve(2);
    expect($secondApproval->id)->toBe($approval->id);
    expect($secondApproval->caused_by)->toBe(2);
    expect($this->post->approvals()->count())->toBe(1);
});

it('can reject with reason and comment in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->reject(1, 'Invalid content', 'Content violates guidelines');

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('rejected');
    expect($approval->caused_by)->toBe(1);
    expect($approval->rejection_reason)->toBe('Invalid content');
    expect($approval->rejection_comment)->toBe('Content violates guidelines');
    expect($approval->responded_at)->not->toBeNull();
});

it('can reject with reason and comment in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $approval = $this->post->reject(1, 'Invalid content', 'Content violates guidelines');

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('rejected');
    expect($approval->rejection_reason)->toBe('Invalid content');
    expect($approval->rejection_comment)->toBe('Content violates guidelines');

    // İkinci kez çağrıldığında mevcut kaydı güncellemeli
    $secondApproval = $this->post->reject(2, 'Updated reason', 'Updated comment');
    expect($secondApproval->id)->toBe($approval->id);
    expect($secondApproval->caused_by)->toBe(2);
    expect($secondApproval->rejection_reason)->toBe('Updated reason');
    expect($secondApproval->rejection_comment)->toBe('Updated comment');
    expect($this->post->approvals()->count())->toBe(1);
});

it('uses authenticated user id when caused_by is null', function () {
    config(['approvals.default.mode' => 'insert']);

    Auth::shouldReceive('id')->andReturn(123);

    $approval = $this->post->approve();

    expect($approval->caused_by)->toBe(123);
});
