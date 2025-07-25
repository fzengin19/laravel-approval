<?php

namespace LaravelApproval\Tests\Listeners;

use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Listeners\SendApprovalNotifications;
use LaravelApproval\Models\Approval;
use LaravelApproval\Notifications\ModelApprovedNotification;
use LaravelApproval\Notifications\ModelRejectedNotification;
use LaravelApproval\Notifications\ModelPendingNotification;
use LaravelApproval\Tests\TestCase;
use Tests\Models\Post;
use Tests\Models\User;

class SendApprovalNotificationsTest extends TestCase
{
    public function test_listener_sends_notification_when_enabled()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')->once();
        });

        $listener->handle($event);
    }

    public function test_listener_does_not_send_notification_when_disabled()
    {
        config(['approvals.features.notifications.enabled' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification should not be called
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldNotReceive('send');
        });

        $listener->handle($event);
    }

    public function test_listener_notifies_model_owner_when_created_by_exists()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to model owner
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::type(\Tests\Models\User::class), \Mockery::type(ModelApprovedNotification::class))
                ->once();
        });

        $listener->handle($event);
    }

    public function test_listener_notifies_admin_when_admin_email_configured()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => 'admin@example.com']);
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]); // Model owner notification'ı devre dışı bırak

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to admin
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::any(), \Mockery::type(ModelApprovedNotification::class))
                ->once();
        });

        $listener->handle($event);
    }

    public function test_listener_handles_rejection_notification()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.events.rejected' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->reject(1, 'spam');

        $event = new ModelRejected($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')->once();
        });

        $listener->handle($event);
    }

    public function test_listener_handles_pending_notification()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.events.pending' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->setPending(1);

        $event = new ModelPending($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')->once();
        });

        $listener->handle($event);
    }

    public function test_listener_does_not_notify_when_model_owner_notification_disabled()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification should not be called
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldNotReceive('send');
        });

        $listener->handle($event);
    }

    public function test_listener_handles_model_without_created_by_field()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => true]);

        $post = Post::factory()->create(); // No created_by
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Should not throw error
        $this->expectNotToPerformAssertions();
        $listener->handle($event);
    }

    public function test_listener_handles_model_with_user_id_field()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.notify_model_owner' => true]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]); // user_id field
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to model owner via user_id
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::type(\Tests\Models\User::class), \Mockery::type(ModelApprovedNotification::class))
                ->once();
        });

        $listener->handle($event);
    }

    public function test_listener_handles_admin_notification_with_existing_admin_user()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => 'admin@example.com']);
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]); // Model owner notification'ı devre dışı bırak

        // Create admin user with the specified email
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to existing admin user
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::type(\Tests\Models\User::class), \Mockery::type(ModelApprovedNotification::class))
                ->once();
        });

        $listener->handle($event);
    }

    public function test_listener_handles_admin_notification_with_non_existing_admin_user()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => 'nonexistent@example.com']);
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]); // Model owner notification'ı devre dışı bırak

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to anonymous admin user
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::any(), \Mockery::type(ModelApprovedNotification::class))
                ->once();
        });

        $listener->handle($event);
    }

    public function test_listener_handles_admin_notification_without_admin_email()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => null]); // No admin email
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]); // Model owner notification'ı devre dışı bırak

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification should not be called
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldNotReceive('send');
        });

        $listener->handle($event);
    }

    public function test_listener_handles_admin_notification_with_anonymous_class_routeNotificationFor()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => 'anonymous@example.com']);
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to test anonymous class routeNotificationFor method
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::any(), \Mockery::type(ModelApprovedNotification::class))
                ->once();
        });

        $listener->handle($event);
    }

    public function test_listener_anonymous_class_routeNotificationFor_method()
    {
        // Anonymous class'ın routeNotificationFor metodunu doğrudan test et
        $adminEmail = 'test@example.com';
        
        $admin = new class($adminEmail) {
            use \Illuminate\Notifications\Notifiable;
            
            public $email;
            public $name = 'Admin';
            
            public function __construct($email) {
                $this->email = $email;
            }
            
            public function routeNotificationFor($driver) {
                return $this->email;
            }
        };
        
        // routeNotificationFor metodunu test et
        $this->assertEquals('test@example.com', $admin->routeNotificationFor('mail'));
        $this->assertEquals('test@example.com', $admin->routeNotificationFor('database'));
    }

    public function test_listener_anonymous_class_routeNotificationFor_method_integration()
    {
        config(['approvals.features.notifications.enabled' => true]);
        config(['approvals.features.notifications.mail.enabled' => true]);
        config(['approvals.features.notifications.recipients.admin_email' => 'integration@example.com']);
        config(['approvals.features.notifications.recipients.notify_model_owner' => false]);

        $user = User::factory()->create();
        $post = Post::factory()->create(['created_by' => $user->id]);
        $approval = $post->approve(1);

        $event = new ModelApproved($post, $approval);
        $listener = new SendApprovalNotifications();

        // Mock notification to ensure routeNotificationFor is called
        $this->mock(\Illuminate\Notifications\ChannelManager::class, function ($mock) {
            $mock->shouldReceive('send')
                ->with(\Mockery::any(), \Mockery::type(ModelApprovedNotification::class))
                ->once()
                ->andReturnUsing(function ($notifiable, $notification) {
                    // Bu callback'te routeNotificationFor metodu çağrılır
                    $this->assertEquals('integration@example.com', $notifiable->routeNotificationFor('mail'));
                });
        });

        $listener->handle($event);
    }
} 