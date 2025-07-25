<?php

use LaravelApproval\Models\Approval;
use Tests\Models\Post;

beforeEach(function () {
    $this->post = Post::create([
        'title' => 'Test Post',
        'content' => 'Test Content',
    ]);
});

it('can create an approval record', function () {
    $approval = Approval::create([
        'approvable_type' => Post::class,
        'approvable_id' => $this->post->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    expect($approval)->toBeInstanceOf(Approval::class);
    expect($approval->approvable_type)->toBe(Post::class);
    expect($approval->approvable_id)->toBe($this->post->id);
    expect($approval->status)->toBe('pending');
});

it('can access the approvable relationship', function () {
    $approval = Approval::create([
        'approvable_type' => Post::class,
        'approvable_id' => $this->post->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    expect($approval->approvable)->toBeInstanceOf(Post::class);
    expect($approval->approvable->id)->toBe($this->post->id);
    expect($approval->approvable->title)->toBe('Test Post');
});

it('can scope by status', function () {
    Approval::create([
        'approvable_type' => Post::class,
        'approvable_id' => $this->post->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    Approval::create([
        'approvable_type' => Post::class,
        'approvable_id' => $this->post->id,
        'status' => 'approved',
        'caused_by' => 1,
    ]);

    expect(Approval::pending()->count())->toBe(1);
    expect(Approval::approved()->count())->toBe(1);
    expect(Approval::rejected()->count())->toBe(0);
});
