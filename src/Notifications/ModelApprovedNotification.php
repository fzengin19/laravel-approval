<?php

namespace LaravelApproval\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LaravelApproval\Models\Approval;

class ModelApprovedNotification extends Notification implements ShouldQueue
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
        
        if (config('approvals.features.notifications.mail.enabled', false)) {
            $channels[] = 'mail';
        }
        
        if (config('approvals.features.notifications.database.enabled', false)) {
            $channels[] = 'database';
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
            ->subject("✅ {$modelName} Approved")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$modelName} (ID: {$modelId}) has been successfully approved.")
            ->line("Approved by: " . ($this->approval->caused_by ? "User ID: {$this->approval->caused_by}" : "System"))
            ->line("Approval Date: " . $this->approval->created_at->format('Y-m-d H:i'))
            ->action('View Details', url('/admin/approvals/' . $this->approval->id))
            ->line('This action was performed automatically.');
            
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
            'type' => 'model_approved',
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id ?? null,
            'approval_id' => $this->approval->id,
            'caused_by' => $this->approval->caused_by,
            'approved_at' => $this->approval->created_at,
            'message' => class_basename($this->model) . ' onaylandı',
        ];
    }
} 