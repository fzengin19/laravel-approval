<?php

namespace LaravelApproval\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LaravelApproval\Models\Approval;

class ModelPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $model;
    public $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct($model, Approval $approval)
    {
        $this->model = $model;
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Pending notification'ları opsiyonel olarak aktif edilebilir
        if (config('approvals.features.notifications.events.pending', false)) {
            if (config('approvals.features.notifications.mail.enabled', false)) {
                $channels[] = 'mail';
            }
            
            if (config('approvals.features.notifications.database.enabled', false)) {
                $channels[] = 'database';
            }
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $modelName = class_basename($this->model);
        $modelId = $this->model->id ?? 'N/A';
        
        // Custom mail template kullanılabilir
        $template = config('approvals.features.notifications.mail.template', null);
        
        $mailMessage = (new MailMessage)
            ->subject("⏳ {$modelName} Pending Approval")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$modelName} (ID: {$modelId}) is pending approval.")
            ->line("Pending Date: " . $this->approval->created_at->format('Y-m-d H:i'))
            ->line("This item will be activated once approved.")
            ->action('Approval Management', url('/admin/approvals'))
            ->line('You will be notified about the approval process.');
            
        // Custom template varsa kullan
        if ($template) {
            $mailMessage->view($template, [
                'model' => $this->model,
                'approval' => $this->approval,
                'notifiable' => $notifiable,
                'approvable' => $this->approval->approvable, // Model ilişkisi
            ]);
        }
        
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'model_pending',
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id ?? null,
            'approval_id' => $this->approval->id,
            'caused_by' => $this->approval->caused_by,
            'pending_at' => $this->approval->created_at,
            'message' => class_basename($this->model) . ' onay bekliyor',
        ];
    }
} 