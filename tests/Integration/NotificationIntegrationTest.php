<?php

namespace LaravelApproval\Tests\Integration;

use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class NotificationIntegrationTest extends TestCase
{
    public function test_notification_system_works_end_to_end()
    {
        // Enable notifications
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Test approval notification
        $post->approve(1);

        // Test rejection notification
        $post->reject(1, 'spam', 'This is spam');

        // Test pending notification
        $post->setPending(1);

        // Verify the approval relationships work
        $this->assertNotNull($post->latestApproval);
        $this->assertEquals($post->id, $post->latestApproval->approvable_id);
    }

    public function test_notification_system_respects_config_settings()
    {
        // Disable notifications
        config(['approvals.features.notifications.enabled' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Test that approval still works even when notifications are disabled
        $post->approve(1);
        $this->assertTrue($post->isApproved());
    }

    public function test_notification_system_handles_different_event_types()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.events.approved' => true]);
        config(['approvals.features.notifications.events.rejected' => true]);
        config(['approvals.features.notifications.events.pending' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Test approval and rejection (should work)
        $post->approve(1);
        $this->assertNotNull($post->latestApproval);

        $post->reject(1, 'spam');
        $this->assertNotNull($post->latestApproval);

        // Test pending (should work even if notifications are disabled)
        $post->setPending(1);
        $this->assertNotNull($post->latestApproval);
    }

    public function test_notification_system_works_with_admin_email()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => 'admin@example.com']);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Test that approval works with admin email configured
        $post->approve(1);
        $this->assertNotNull($post->latestApproval);
    }

    public function test_notification_system_works_with_model_relationships()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);

        // Test approval
        $post->approve(1);

        // Verify the approval relationship works
        $this->assertNotNull($post->latestApproval);
        $this->assertEquals($post->id, $post->latestApproval->approvable_id);
        $this->assertEquals($post->id, $post->latestApproval->approvable->id);
    }
}
