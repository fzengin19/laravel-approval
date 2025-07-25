<?php

namespace LaravelApproval\Tests\Notifications;

use LaravelApproval\Models\Approval;
use LaravelApproval\Notifications\ModelApprovedNotification;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class ModelApprovedNotificationTest extends TestCase
{
    public function test_notification_has_correct_mail_subject()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $notification = new ModelApprovedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('✅ Post Approved', $mailMessage->subject);
    }

    public function test_notification_has_correct_mail_content()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'created_by' => $user->id
        ]);
        $approval = $post->approve(1);

        $notification = new ModelApprovedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Hello John Doe!', $mailMessage->greeting);
        $this->assertStringContainsString('Post (ID: ' . $post->id . ') has been successfully approved.', $mailMessage->introLines[0]);
        $this->assertStringContainsString('Approved by: User ID: 1', $mailMessage->introLines[1]);
    }

    public function test_notification_uses_custom_template_when_configured()
    {
        config(['approvals.features.notifications.mail.template' => 'custom.template']);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $notification = new ModelApprovedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        // Custom template kullanıldığını kontrol et
        $this->assertTrue(method_exists($mailMessage, 'view'));
    }

    public function test_notification_has_correct_array_representation()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $notification = new ModelApprovedNotification($post, $approval);
        $array = $notification->toArray($user);

        $this->assertEquals('model_approved', $array['type']);
        $this->assertEquals(Post::class, $array['model_type']);
        $this->assertEquals($post->id, $array['model_id']);
        $this->assertEquals($approval->id, $array['approval_id']);
        $this->assertEquals(1, $array['caused_by']);
        $this->assertEquals('Post onaylandı', $array['message']);
    }

    public function test_notification_via_method_returns_correct_channels()
    {
        // Mail enabled
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.database.enabled' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $notification = new ModelApprovedNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertNotContains('database', $channels);

        // Database enabled
        config(['approvals.features.notifications.mail.enabled' => false]);
        config(['approvals.features.notifications.database.enabled' => true]);

        $notification = new ModelApprovedNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertNotContains('mail', $channels);

        // Both enabled
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.database.enabled' => true]);

        $notification = new ModelApprovedNotification($post, $approval);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_notification_handles_system_approval()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(null); // System approval

        $notification = new ModelApprovedNotification($post, $approval);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Approved by: System', $mailMessage->introLines[1]);
    }
} 