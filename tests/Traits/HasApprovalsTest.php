<?php

use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\Approvable;
use Tests\Models\Post;

// Test için Post modelini Approvable trait'i ile genişlet
class TestPost extends Post
{
    use Approvable;

    protected $table = 'posts';
}

beforeEach(function () {
    $this->post = TestPost::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('can access approvals relationship', function () {
    // 3 farklı approval kaydı oluştur
    Approval::create([
        'approvable_type' => TestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    Approval::create([
        'approvable_type' => TestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'approved',
        'caused_by' => 1,
    ]);

    Approval::create([
        'approvable_type' => TestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'rejected',
        'caused_by' => 1,
    ]);

    expect($this->post->approvals()->count())->toBe(3);
});

it('can access latest approval relationship', function () {
    // İlk approval
    $firstApproval = Approval::create([
        'approvable_type' => TestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    // İkinci approval (daha yeni)
    $secondApproval = Approval::create([
        'approvable_type' => TestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'approved',
        'caused_by' => 1,
    ]);

    // En son approval (en yeni)
    $latestApproval = Approval::create([
        'approvable_type' => TestPost::class,
        'approvable_id' => $this->post->id,
        'status' => 'rejected',
        'caused_by' => 1,
    ]);

    expect($this->post->latestApproval)->toBeInstanceOf(Approval::class);
    expect($this->post->latestApproval->id)->toBe($latestApproval->id);
    expect($this->post->latestApproval->status)->toBe('rejected');
});
