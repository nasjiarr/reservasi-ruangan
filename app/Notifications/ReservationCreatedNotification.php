<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation
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
            ->subject('Pengajuan Reservasi Ruangan Baru: ' . $this->reservation->room->name)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada permohonan reservasi ruangan baru yang memerlukan persetujuan Anda.')
            ->line('**Pemesan:** ' . $this->reservation->user->name)
            ->line('**Ruangan:** ' . $this->reservation->room->name)
            ->line('**Kegiatan:** ' . $this->reservation->title)
            ->line('**Waktu:** ' . $this->reservation->start_time->format('d M Y H:i') . ' s/d ' . $this->reservation->end_time->format('d M Y H:i'))
            ->action('Tinjau Reservasi', url('/approvals'))
            ->line('Terima kasih telah menggunakan sistem reservasi ruangan.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pengajuan Reservasi Baru',
            'message' => "{$this->reservation->user->name} mengajukan reservasi ruangan {$this->reservation->room->name}.",
            'reservation_id' => $this->reservation->id,
            'room_name' => $this->reservation->room->name,
            'user_name' => $this->reservation->user->name,
            'start_time' => $this->reservation->start_time->format('d M Y H:i'),
            'end_time' => $this->reservation->end_time->format('d M Y H:i'),
            'url' => '/approvals',
            'type' => 'created',
        ];
    }
}

