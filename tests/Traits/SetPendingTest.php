<?php

use Illuminate\Support\Facades\Auth;
use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\HasApprovals;
use Workbench\App\Models\Post;

// Test için Post modelini HasApprovals trait'i ile genişlet
class SetPendingTestPost extends Post
{
    use HasApprovals;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = SetPendingTestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('creates new approval record in insert mode', function () {
    config(['approvals.default.mode' => 'insert']);

    $approval = $this->post->setPending(1);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('pending');
    expect($approval->caused_by)->toBe(1);
    expect($approval->responded_at)->not->toBeNull();

    // İkinci kez çağrıldığında yeni kayıt oluşturmalı
    $secondApproval = $this->post->setPending(2);
    expect($secondApproval->id)->not->toBe($approval->id);
    expect($this->post->approvals()->count())->toBe(2);
});

it('updates existing approval record in upsert mode', function () {
    config(['approvals.default.mode' => 'upsert']);

    $approval = $this->post->setPending(1);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->status)->toBe('pending');
    expect($approval->caused_by)->toBe(1);

    // İkinci kez çağrıldığında mevcut kaydı güncellemeli
    $secondApproval = $this->post->setPending(2);
    expect($secondApproval->id)->toBe($approval->id);
    expect($secondApproval->caused_by)->toBe(2);
    expect($this->post->approvals()->count())->toBe(1);
});

it('uses authenticated user id when caused_by is null', function () {
    config(['approvals.default.mode' => 'insert']);

    // Mock authenticated user
    $user = new class
    {
        public function getAuthIdentifier()
        {
            return 123;
        }
    };

    Auth::shouldReceive('id')->andReturn(123);

    $approval = $this->post->setPending();

    expect($approval->caused_by)->toBe(123);
});
