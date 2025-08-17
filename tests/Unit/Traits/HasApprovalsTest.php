<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;
use LaravelApproval\LaravelApproval\Tests\Models\Post;
use LaravelApproval\LaravelApproval\Tests\TestCase;

class HasApprovalsTest extends TestCase
{
    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->post = Post::factory()->create();
    }

    /** @test */
    public function it_has_approvals_relationship(): void
    {
        $relationship = $this->post->approvals();

        expect($relationship)->toBeInstanceOf(MorphMany::class);
        expect($relationship->getRelated())->toBeInstanceOf(Approval::class);
        expect($relationship->getMorphType())->toBe('approvable_type');
        expect($relationship->getForeignKeyName())->toBe('approvable_id');
    }

    /** @test */
    public function it_has_latest_approval_relationship(): void
    {
        $relationship = $this->post->latestApproval();

        expect($relationship)->toBeInstanceOf(MorphOne::class);
        expect($relationship->getRelated())->toBeInstanceOf(Approval::class);
        expect($relationship->getMorphType())->toBe('approvable_type');
        expect($relationship->getForeignKeyName())->toBe('approvable_id');
    }

    /** @test */
    public function it_can_get_all_approvals(): void
    {
        // Create multiple approvals
        Approval::factory()->count(3)->for($this->post, 'approvable')->create();

        $approvals = $this->post->approvals;

        expect($approvals)->toHaveCount(3);
        expect($approvals->first())->toBeInstanceOf(Approval::class);
    }

    /** @test */
    public function it_can_get_latest_approval(): void
    {
        // Create multiple approvals with different timestamps
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        $latestApproval = Approval::factory()
            ->for($this->post, 'approvable')
            ->pending()
            ->create();

        $this->post->refresh();
        /** @var Approval $retrievedLatest */
        $retrievedLatest = $this->post->latestApproval;

        expect($retrievedLatest)->toBeInstanceOf(Approval::class);
        expect($retrievedLatest->getKey())->toBe($latestApproval->getKey());
        expect($retrievedLatest->getAttribute('status'))->toBe(ApprovalStatus::PENDING);
    }

    /** @test */
    public function it_returns_true_when_model_is_approved(): void
    {
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        expect($this->post->isApproved())->toBeTrue();
        expect($this->post->isPending())->toBeFalse();
        expect($this->post->isRejected())->toBeFalse();
    }

    /** @test */
    public function it_returns_true_when_model_is_pending(): void
    {
        Approval::factory()
            ->for($this->post, 'approvable')
            ->pending()
            ->create();

        expect($this->post->isPending())->toBeTrue();
        expect($this->post->isApproved())->toBeFalse();
        expect($this->post->isRejected())->toBeFalse();
    }

    /** @test */
    public function it_returns_true_when_model_is_rejected(): void
    {
        Approval::factory()
            ->for($this->post, 'approvable')
            ->rejected()
            ->create();

        expect($this->post->isRejected())->toBeTrue();
        expect($this->post->isApproved())->toBeFalse();
        expect($this->post->isPending())->toBeFalse();
    }

    /** @test */
    public function it_returns_false_for_all_status_checks_when_no_approval_exists(): void
    {
        expect($this->post->isApproved())->toBeFalse();
        expect($this->post->isPending())->toBeFalse();
        expect($this->post->isRejected())->toBeFalse();
    }

    /** @test */
    public function it_uses_latest_approval_for_status_checks(): void
    {
        // Create old approved approval
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        // Create latest rejected approval
        Approval::factory()
            ->for($this->post, 'approvable')
            ->rejected()
            ->create();

        $this->post->refresh();

        // Should use latest (rejected) status
        expect($this->post->isRejected())->toBeTrue();
        expect($this->post->isApproved())->toBeFalse();
        expect($this->post->isPending())->toBeFalse();
    }

    /** @test */
    public function it_can_get_current_approval_status(): void
    {
        // No approval
        expect($this->post->getApprovalStatus())->toBeNull();

        // With approved approval
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        $this->post->refresh();
        expect($this->post->getApprovalStatus())->toBe(ApprovalStatus::APPROVED);
    }

    /** @test */
    public function it_returns_latest_approval_status(): void
    {
        // Create old pending approval
        Approval::factory()
            ->for($this->post, 'approvable')
            ->pending()
            ->create();

        // Create latest approved approval
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        $this->post->refresh();
        expect($this->post->getApprovalStatus())->toBe(ApprovalStatus::APPROVED);
    }

    /** @test */
    public function it_handles_multiple_approvals_correctly(): void
    {
        // Create a sequence of approvals
        Approval::factory()
            ->for($this->post, 'approvable')
            ->pending()
            ->create();

        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        Approval::factory()
            ->for($this->post, 'approvable')
            ->rejected()
            ->create();

        $this->post->refresh();

        // Should reflect the latest status (rejected)
        expect($this->post->isRejected())->toBeTrue();
        expect($this->post->getApprovalStatus())->toBe(ApprovalStatus::REJECTED);
        expect($this->post->approvals)->toHaveCount(3);
    }

    /** @test */
    public function it_works_with_different_approvable_models(): void
    {
        $anotherPost = Post::factory()->create();

        // Create approval for first post
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        // Create approval for second post
        Approval::factory()
            ->for($anotherPost, 'approvable')
            ->rejected()
            ->create();

        // Each post should have its own approval status
        expect($this->post->isApproved())->toBeTrue();
        expect($anotherPost->isRejected())->toBeTrue();

        // Each post should only see its own approvals
        expect($this->post->approvals)->toHaveCount(1);
        expect($anotherPost->approvals)->toHaveCount(1);
    }

    /** @test */
    public function it_can_eager_load_approvals(): void
    {
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        /** @var Post $postWithApprovals */
        $postWithApprovals = Post::with('approvals')->find($this->post->id);

        expect($postWithApprovals)->not->toBeNull();
        expect($postWithApprovals->relationLoaded('approvals'))->toBeTrue();
        expect($postWithApprovals->approvals)->toHaveCount(1);
    }

    /** @test */
    public function it_can_eager_load_latest_approval(): void
    {
        Approval::factory()
            ->for($this->post, 'approvable')
            ->approved()
            ->create();

        /** @var Post $postWithLatestApproval */
        $postWithLatestApproval = Post::with('latestApproval')->find($this->post->id);

        expect($postWithLatestApproval)->not->toBeNull();
        expect($postWithLatestApproval->relationLoaded('latestApproval'))->toBeTrue();
        expect($postWithLatestApproval->latestApproval)->toBeInstanceOf(Approval::class);
    }
}
