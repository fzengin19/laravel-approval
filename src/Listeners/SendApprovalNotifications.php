<?php

namespace LaravelApproval\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelPending;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Notifications\ModelApprovedNotification;
use LaravelApproval\Notifications\ModelPendingNotification;
use LaravelApproval\Notifications\ModelRejectedNotification;

class SendApprovalNotifications implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if (! config('approvals.features.notifications.enabled', false)) {
            return;
        }

        $model = $event->model;
        $approval = $event->approval;

        // Model sahibine bildirim gönder
        if (config('approvals.features.notifications.recipients.notify_model_owner', true)) {
            $this->notifyModelOwner($event, $model, $approval);
        }

        // Admin'e bildirim gönder
        $this->notifyAdmin($event, $model, $approval);
    }

    /**
     * Model sahibine bildirim gönder
     */
    protected function notifyModelOwner($event, $model, $approval): void
    {
        // Approvable model'in created_by alanı varsa o kullanıcıya bildirim gönder
        $approvable = $approval->approvable;
        if ($approvable && isset($approvable->created_by) && $approvable->created_by) {
            $userClass = config('auth.providers.users.model', \App\Models\User::class);
            $user = $userClass::find($approvable->created_by);
            if ($user) {
                $this->sendNotification($event, $user, $model, $approval);
            }
        }

        // Approvable model'in user_id alanı varsa o kullanıcıya bildirim gönder (opsiyonel)
        if ($approvable && isset($approvable->user_id) && $approvable->user_id) {
            $userClass = config('auth.providers.users.model', \App\Models\User::class);
            $user = $userClass::find($approvable->user_id);
            if ($user) {
                $this->sendNotification($event, $user, $model, $approval);
            }
        }
    }

    /**
     * Admin'e bildirim gönder
     */
    protected function notifyAdmin($event, $model, $approval): void
    {
        $adminEmail = config('approvals.features.notifications.recipients.admin_email');

        if ($adminEmail) {
            // Admin kullanıcısını bul veya oluştur
            $userClass = config('auth.providers.users.model', \App\Models\User::class);
            $admin = $userClass::where('email', $adminEmail)->first();

            if (! $admin) {
                // Admin kullanıcısı yoksa, geçici bir notifiable oluştur
                $admin = new class($adminEmail)
                {
                    use \Illuminate\Notifications\Notifiable;

                    public $email;

                    public $name = 'Admin';

                    public function __construct($email)
                    {
                        $this->email = $email;
                    }

                    public function routeNotificationFor($driver)
                    {
                        return $this->email;
                    }
                };
            }

            $this->sendNotification($event, $admin, $model, $approval);
        }
    }

    /**
     * Uygun notification'ı gönder
     */
    protected function sendNotification($event, $notifiable, $model, $approval): void
    {
        if ($event instanceof ModelApproved && config('approvals.features.notifications.events.approved', true)) {
            $notifiable->notify(new ModelApprovedNotification($model, $approval));
        }

        if ($event instanceof ModelRejected && config('approvals.features.notifications.events.rejected', true)) {
            $notifiable->notify(new ModelRejectedNotification($model, $approval));
        }

        if ($event instanceof ModelPending && config('approvals.features.notifications.events.pending', false)) {
            $notifiable->notify(new ModelPendingNotification($model, $approval));
        }
    }
}
