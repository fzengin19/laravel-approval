<?php

namespace LaravelApproval\Tests\Facades;

use LaravelApproval\Enums\ApprovalStatus;
use LaravelApproval\Facades\Approval;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class ApprovalTest extends TestCase
{
    private Post $post;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->post = Post::factory()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_approve_a_model_through_facade()
    {
        Approval::approve($this->post, $this->user->id);

        $this->post->refresh();
        $this->assertTrue($this->post->isApproved());
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => $this->post->getMorphClass(),
            'approvable_id' => $this->post->id,
            'status' => ApprovalStatus::APPROVED->value,
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
        ]);
    }

    /** @test */
    public function it_can_reject_a_model_through_facade()
    {
        Approval::reject($this->post, $this->user->id, 'Invalid content', 'Content violates guidelines');

        $this->post->refresh();
        $this->assertTrue($this->post->isRejected());
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => $this->post->getMorphClass(),
            'approvable_id' => $this->post->id,
            'status' => ApprovalStatus::REJECTED->value,
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
            'rejection_reason' => 'other',
            'rejection_comment' => 'Invalid content - Content violates guidelines',
        ]);
    }

    /** @test */
    public function it_can_set_pending_through_facade()
    {
        Approval::setPending($this->post, $this->user->id);

        $this->post->refresh();
        $this->assertTrue($this->post->isPending());
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => $this->post->getMorphClass(),
            'approvable_id' => $this->post->id,
            'status' => ApprovalStatus::PENDING->value,
            'caused_by_id' => $this->user->id,
            'caused_by_type' => $this->user->getMorphClass(),
        ]);
    }

    /** @test */
    public function it_can_get_statistics_for_a_model_class()
    {
        Approval::approve(Post::factory()->create(), $this->user->id);
        Approval::setPending(Post::factory()->create(), $this->user->id);
        Approval::reject(Post::factory()->create(), $this->user->id, 'Invalid');

        $statistics = Approval::getStatistics(Post::class);

        $this->assertEquals(4, $statistics['total']); // 1 from setUp + 3 from this test
        $this->assertEquals(1, $statistics['approved']);
        $this->assertEquals(1, $statistics['pending']);
        $this->assertEquals(1, $statistics['rejected']);
    }

    /** @test */
    public function it_can_get_all_statistics()
    {
        config(['approvals.models' => [Post::class => []]]);

        Approval::approve(Post::factory()->create(), $this->user->id);

        $allStatistics = Approval::getAllStatistics();

        $this->assertArrayHasKey(Post::class, $allStatistics);
        $this->assertArrayHasKey('total', $allStatistics[Post::class]);
    }
}
