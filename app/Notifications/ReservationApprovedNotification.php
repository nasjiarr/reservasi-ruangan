<?php

namespace App\Notifications;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation,
        public User $approver
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reservasi Ruangan Disetujui: ' . $this->reservation->room->name)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Kabar baik! Permohonan reservasi ruangan Anda telah disetujui.')
            ->line('**Ruangan:** ' . $this->reservation->room->name)
            ->line('**Kegiatan:** ' . $this->reservation->title)
            ->line('**Waktu:** ' . $this->reservation->start_time->format('d M Y H:i') . ' s/d ' . $this->reservation->end_time->format('d M Y H:i'))
            ->line('**Disetujui Oleh:** ' . $this->approver->name)
            ->action('Lihat Detail Reservasi', url('/reservations/' . $this->reservation->id))
            ->line('Harap hadir tepat waktu dan menjaga kebersihan ruangan.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reservasi Disetujui',
            'message' => "Reservasi Anda untuk {$this->reservation->room->name} telah disetujui oleh {$this->approver->name}.",
            'reservation_id' => $this->reservation->id,
            'room_name' => $this->reservation->room->name,
            'approver_name' => $this->approver->name,
            'start_time' => $this->reservation->start_time->format('d M Y H:i'),
            'end_time' => $this->reservation->end_time->format('d M Y H:i'),
            'url' => '/reservations/' . $this->reservation->id,
            'type' => 'approved',
        ];
    }
}

