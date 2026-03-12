<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Rental;

class RentalStatusChanged extends Notification
{
    use Queueable;

    public $rental;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(Rental $rental, $status)
    {
        $this->rental = $rental;
        $this->status = $status;
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
        $emoji = match ($this->status) {
            'approved' => '✅',
            'rejected' => '❌',
            'completed' => '🏁',
            'cancelled' => '🚫',
            'active' => '🚗',
            default => 'ℹ️'
        };

        return [
            'rental_id' => $this->rental->id,
            'status' => $this->status,
            'car_model' => $this->rental->car ? ($this->rental->car->brand . ' ' . $this->rental->car->model) : 'Car',
            'message' => $emoji . ' Your rental request for ' . ($this->rental->car->model ?? 'car') . ' has been ' . $this->status,
            'type' => 'status_changed'
        ];
    }
}
