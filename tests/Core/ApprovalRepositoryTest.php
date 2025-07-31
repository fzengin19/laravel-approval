<?php

namespace LaravelApproval\Tests\Core;

use LaravelApproval\Core\ApprovalRepository;
use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Models\Approval;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class ApprovalRepositoryTest extends TestCase
{
    private ApprovalRepository $repo;

    private Post $post;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(ApprovalRepository::class);
        $this->post = Post::factory()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_and_update_or_create_approvals()
    {
        $data = [
            'status' => ApprovalStatus::PENDING,
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
        ];
        $approval = $this->repo->create($this->post, $data);
        $this->assertInstanceOf(Approval::class, $approval);
        $this->assertEquals(ApprovalStatus::PENDING, $approval->status);

        $updatedData = [
            'status' => ApprovalStatus::APPROVED,
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
        ];
        $updated = $this->repo->updateOrCreate($this->post, $updatedData);
        $this->assertEquals(ApprovalStatus::APPROVED, $updated->status);
    }

    /** @test */
    public function it_gets_latest_for_model()
    {
        $this->assertNull($this->repo->getLatestForModel($this->post));

        Approval::factory()->create([
            'approvable_id' => $this->post->id,
            'approvable_type' => $this->post->getMorphClass(),
            'status' => ApprovalStatus::APPROVED,
        ]);
        $latest = $this->repo->getLatestForModel($this->post);
        $this->assertInstanceOf(Approval::class, $latest);
        $this->assertEquals(ApprovalStatus::APPROVED, $latest->status);
    }
}
