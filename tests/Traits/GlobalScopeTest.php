<?php

use LaravelApproval\Models\Approval;
use LaravelApproval\Traits\HasApprovals;
use Workbench\App\Models\Post;

// Test için Post modelini HasApprovals trait'i ile genişlet
class GlobalScopeTestPost extends Post
{
    use HasApprovals;

    protected $table = 'posts';
}

beforeEach(function () {
    // Onaylı post oluştur
    $approvedPost = GlobalScopeTestPost::create([
        'title' => 'Approved Post',
        'content' => 'Content',
    ]);

    Approval::create([
        'approvable_type' => GlobalScopeTestPost::class,
        'approvable_id' => $approvedPost->id,
        'status' => 'approved',
        'caused_by' => 1,
    ]);

    // Beklemede post oluştur
    $pendingPost = GlobalScopeTestPost::create([
        'title' => 'Pending Post',
        'content' => 'Content',
    ]);

    Approval::create([
        'approvable_type' => GlobalScopeTestPost::class,
        'approvable_id' => $pendingPost->id,
        'status' => 'pending',
        'caused_by' => 1,
    ]);

    // Reddedilmiş post oluştur
    $rejectedPost = GlobalScopeTestPost::create([
        'title' => 'Rejected Post',
        'content' => 'Content',
    ]);

    Approval::create([
        'approvable_type' => GlobalScopeTestPost::class,
        'approvable_id' => $rejectedPost->id,
        'status' => 'rejected',
        'caused_by' => 1,
    ]);
});

it('shows only approved posts when show_only_approved_by_default is true', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $posts = GlobalScopeTestPost::all();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->title)->toBe('Approved Post');
});

it('shows all posts when show_only_approved_by_default is false', function () {
    config(['approvals.default.show_only_approved_by_default' => false]);

    $posts = GlobalScopeTestPost::all();

    expect($posts)->toHaveCount(3);
    expect($posts->pluck('title')->toArray())->toContain('Approved Post');
    expect($posts->pluck('title')->toArray())->toContain('Pending Post');
    expect($posts->pluck('title')->toArray())->toContain('Rejected Post');
});

it('can include unapproved posts with withUnapproved scope', function () {
    config(['approvals.default.show_only_approved_by_default' => true]);

    $posts = GlobalScopeTestPost::withUnapproved()->get();

    expect($posts)->toHaveCount(3);
    expect($posts->pluck('title')->toArray())->toContain('Approved Post');
    expect($posts->pluck('title')->toArray())->toContain('Pending Post');
    expect($posts->pluck('title')->toArray())->toContain('Rejected Post');
});
