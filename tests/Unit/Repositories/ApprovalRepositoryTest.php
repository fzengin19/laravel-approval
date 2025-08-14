<?php

declare(strict_types=1);

namespace LaravelApproval\LaravelApproval\Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelApproval\LaravelApproval\Contracts\ApprovalRepositoryInterface;
use LaravelApproval\LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\LaravelApproval\Models\Approval;
use LaravelApproval\LaravelApproval\Repositories\ApprovalRepository;
use LaravelApproval\LaravelApproval\Tests\Models\Post;
use LaravelApproval\LaravelApproval\Tests\Models\User;
use LaravelApproval\LaravelApproval\Tests\TestCase;

final class ApprovalRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ApprovalRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ApprovalRepository;
    }

    public function test_repository_implements_interface(): void
    {
        $this->assertInstanceOf(ApprovalRepositoryInterface::class, $this->repository);
    }

    public function test_create_approval_with_valid_data(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $data = [
            'approvable_type' => Post::class,
            'approvable_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'status' => ApprovalStatus::PENDING->value,
        ];

        $approval = $this->repository->create($data);

        $this->assertInstanceOf(Approval::class, $approval);
        $this->assertEquals(Post::class, $approval->approvable_type);
        $this->assertEquals($post->id, $approval->approvable_id);
        $this->assertEquals(User::class, $approval->causer_type);
        $this->assertEquals($user->id, $approval->causer_id);
        $this->assertEquals(ApprovalStatus::PENDING, $approval->status);
        $this->assertDatabaseHas('approvals', $data);
    }

    public function test_create_approval_with_enum_status(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $data = [
            'approvable_type' => Post::class,
            'approvable_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'status' => ApprovalStatus::APPROVED,
        ];

        $approval = $this->repository->create($data);

        $this->assertEquals(ApprovalStatus::APPROVED, $approval->status);
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Post::class,
            'approvable_id' => $post->id,
            'status' => ApprovalStatus::APPROVED->value,
        ]);
    }

    public function test_create_approval_with_rejection_data(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $data = [
            'approvable_type' => Post::class,
            'approvable_id' => $post->id,
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'status' => ApprovalStatus::REJECTED->value,
            'rejection_reason' => 'invalid_content',
            'rejection_comment' => 'The content does not meet our standards.',
        ];

        $approval = $this->repository->create($data);

        $this->assertEquals('invalid_content', $approval->rejection_reason);
        $this->assertEquals('The content does not meet our standards.', $approval->rejection_comment);
        $this->assertDatabaseHas('approvals', $data);
    }

    public function test_update_approval(): void
    {
        $approval = Approval::factory()->pending()->create();

        $updateData = [
            'status' => ApprovalStatus::APPROVED->value,
            'causer_type' => User::class,
            'causer_id' => User::factory()->create()->id,
        ];

        $updatedApproval = $this->repository->update($approval->id, $updateData);

        $this->assertEquals(ApprovalStatus::APPROVED, $updatedApproval->status);
        $this->assertEquals($updateData['causer_type'], $updatedApproval->causer_type);
        $this->assertEquals($updateData['causer_id'], $updatedApproval->causer_id);
        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => ApprovalStatus::APPROVED->value,
        ]);
    }

    public function test_update_approval_with_rejection(): void
    {
        $approval = Approval::factory()->pending()->create();

        $updateData = [
            'status' => ApprovalStatus::REJECTED->value,
            'rejection_reason' => 'policy_violation',
            'rejection_comment' => 'Violates community guidelines.',
        ];

        $updatedApproval = $this->repository->update($approval->id, $updateData);

        $this->assertEquals(ApprovalStatus::REJECTED, $updatedApproval->status);
        $this->assertEquals('policy_violation', $updatedApproval->rejection_reason);
        $this->assertEquals('Violates community guidelines.', $updatedApproval->rejection_comment);
    }

    public function test_find_by_model(): void
    {
        $post = Post::factory()->create();
        $approval1 = Approval::factory()->for($post, 'approvable')->create();
        $approval2 = Approval::factory()->for($post, 'approvable')->create();

        // Create approval for different model to ensure filtering
        $otherPost = Post::factory()->create();
        Approval::factory()->for($otherPost, 'approvable')->create();

        $approvals = $this->repository->findByModel($post);

        $this->assertInstanceOf(Collection::class, $approvals);
        $this->assertCount(2, $approvals);
        $this->assertTrue($approvals->contains($approval1));
        $this->assertTrue($approvals->contains($approval2));
    }

    public function test_find_by_model_returns_empty_collection_when_no_approvals(): void
    {
        $post = Post::factory()->create();

        $approvals = $this->repository->findByModel($post);

        $this->assertInstanceOf(Collection::class, $approvals);
        $this->assertTrue($approvals->isEmpty());
    }

    public function test_find_latest_by_model(): void
    {
        $post = Post::factory()->create();

        $oldApproval = Approval::factory()
            ->for($post, 'approvable')
            ->create(['created_at' => now()->subDays(2)]);

        $latestApproval = Approval::factory()
            ->for($post, 'approvable')
            ->create(['created_at' => now()->subDay()]);

        $result = $this->repository->findLatestByModel($post);

        $this->assertInstanceOf(Approval::class, $result);
        $this->assertEquals($latestApproval->id, $result->id);
        $this->assertNotEquals($oldApproval->id, $result->id);
    }

    public function test_find_latest_by_model_returns_null_when_no_approvals(): void
    {
        $post = Post::factory()->create();

        $result = $this->repository->findLatestByModel($post);

        $this->assertNull($result);
    }

    public function test_delete_by_model(): void
    {
        $post = Post::factory()->create();
        $approval1 = Approval::factory()->for($post, 'approvable')->create();
        $approval2 = Approval::factory()->for($post, 'approvable')->create();

        // Create approval for different model to ensure it's not deleted
        $otherPost = Post::factory()->create();
        $otherApproval = Approval::factory()->for($otherPost, 'approvable')->create();

        $result = $this->repository->deleteByModel($post);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('approvals', ['id' => $approval1->id]);
        $this->assertDatabaseMissing('approvals', ['id' => $approval2->id]);
        $this->assertDatabaseHas('approvals', ['id' => $otherApproval->id]);
    }

    public function test_delete_by_model_returns_false_when_no_approvals(): void
    {
        $post = Post::factory()->create();

        $result = $this->repository->deleteByModel($post);

        $this->assertFalse($result);
    }

    public function test_get_by_status(): void
    {
        $approvedApproval1 = Approval::factory()->approved()->create();
        $approvedApproval2 = Approval::factory()->approved()->create();
        $pendingApproval = Approval::factory()->pending()->create();
        $rejectedApproval = Approval::factory()->rejected()->create();

        $approvedApprovals = $this->repository->getByStatus(ApprovalStatus::APPROVED);

        $this->assertInstanceOf(Collection::class, $approvedApprovals);
        $this->assertCount(2, $approvedApprovals);
        $this->assertTrue($approvedApprovals->contains($approvedApproval1));
        $this->assertTrue($approvedApprovals->contains($approvedApproval2));
        $this->assertFalse($approvedApprovals->contains($pendingApproval));
        $this->assertFalse($approvedApprovals->contains($rejectedApproval));
    }

    public function test_get_by_status_with_model_class_filter(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $postApproval = Approval::factory()
            ->approved()
            ->for($post, 'approvable')
            ->create();

        $userApproval = Approval::factory()
            ->approved()
            ->for($user, 'approvable')
            ->create();

        $postApprovals = $this->repository->getByStatus(ApprovalStatus::APPROVED, Post::class);

        $this->assertCount(1, $postApprovals);
        $this->assertTrue($postApprovals->contains($postApproval));
        $this->assertFalse($postApprovals->contains($userApproval));
    }

    public function test_count_by_status(): void
    {
        Approval::factory()->approved()->count(3)->create();
        Approval::factory()->pending()->count(2)->create();
        Approval::factory()->rejected()->count(1)->create();

        $approvedCount = $this->repository->countByStatus(ApprovalStatus::APPROVED);
        $pendingCount = $this->repository->countByStatus(ApprovalStatus::PENDING);
        $rejectedCount = $this->repository->countByStatus(ApprovalStatus::REJECTED);

        $this->assertEquals(3, $approvedCount);
        $this->assertEquals(2, $pendingCount);
        $this->assertEquals(1, $rejectedCount);
    }

    public function test_count_by_status_with_model_class_filter(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        Approval::factory()
            ->approved()
            ->for($post, 'approvable')
            ->count(2)
            ->create();

        Approval::factory()
            ->approved()
            ->for($user, 'approvable')
            ->count(3)
            ->create();

        $postApprovedCount = $this->repository->countByStatus(ApprovalStatus::APPROVED, Post::class);
        $userApprovedCount = $this->repository->countByStatus(ApprovalStatus::APPROVED, User::class);

        $this->assertEquals(2, $postApprovedCount);
        $this->assertEquals(3, $userApprovedCount);
    }

    public function test_exists_returns_true_when_approval_exists(): void
    {
        $post = Post::factory()->create();
        Approval::factory()->for($post, 'approvable')->create();

        $exists = $this->repository->exists($post);

        $this->assertTrue($exists);
    }

    public function test_exists_returns_false_when_no_approval_exists(): void
    {
        $post = Post::factory()->create();

        $exists = $this->repository->exists($post);

        $this->assertFalse($exists);
    }

    public function test_repository_handles_large_datasets(): void
    {
        $posts = Post::factory()->count(50)->create();

        foreach ($posts as $post) {
            Approval::factory()->pending()->for($post, 'approvable')->create();
        }

        $allApprovals = $this->repository->getByStatus(ApprovalStatus::PENDING);
        $totalCount = $this->repository->countByStatus(ApprovalStatus::PENDING);

        $this->assertCount(50, $allApprovals);
        $this->assertEquals(50, $totalCount);
    }
}
