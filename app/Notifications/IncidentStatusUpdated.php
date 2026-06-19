<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentStatusUpdated extends Notification
{
    use Queueable;

    protected $incident;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($incident, $newStatus)
    {
        $this->incident = $incident;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $categoryLabels = [
            'unsafe_condition' => 'Kondisi Tidak Aman',
            'unsafe_act' => 'Tindakan Tidak Aman',
            'near_miss' => 'Hampir Celaka (Near-Miss)',
            'positive_observation' => 'Observasi Positif',
        ];

        $label = $categoryLabels[$this->incident->category] ?? $this->incident->category;

        $title = 'Status Laporan Diperbarui';
        $message = "Laporan Anda terkait {$label} telah mengalami perubahan status.";

        if ($this->newStatus === 'investigating') {
            $message = "Laporan Anda terkait {$label} saat ini sedang diinvestigasi.";
        } elseif ($this->newStatus === 'closed') {
            $title = 'Laporan Insiden Diselesaikan';
            $message = "Terima kasih, laporan terkait {$label} telah selesai ditindaklanjuti.";
        }

        return [
            'incident_id' => $this->incident->id,
            'category' => $this->incident->category,
            'admin_feedback' => $this->incident->admin_feedback,
            'new_status' => $this->newStatus,
            'title' => $title,
            'message' => $message,
        ];
    }
}
