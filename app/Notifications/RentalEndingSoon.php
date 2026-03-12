<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Rental;

class RentalEndingSoon extends Notification
{
    use Queueable;

    public $rental;

    /**
     * Create a new notification instance.
     */
    public function __construct(Rental $rental)
    {
        $this->rental = $rental;
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
        return [
            'rental_id' => $this->rental->id,
            'car_model' => $this->rental->car ? ($this->rental->car->brand . ' ' . $this->rental->car->model) : 'Car',
            'end_date' => $this->rental->end_date,
            'message' => '⏳ Reminder: Your rental for ' . ($this->rental->car->model ?? 'car') . ' ends tomorrow.',
            'type' => 'ending_soon'
        ];
    }
}
