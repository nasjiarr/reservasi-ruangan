<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Reservation $reservation,
        public string $note
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
            ->subject('Pemberitahuan: Reservasi Ruangan Ditolak')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Mohon maaf, permohonan reservasi ruangan Anda belum dapat disetujui.')
            ->line('**Ruangan:** ' . $this->reservation->room->name)
            ->line('**Kegiatan:** ' . $this->reservation->title)
            ->line('**Waktu:** ' . $this->reservation->start_time->format('d M Y H:i') . ' s/d ' . $this->reservation->end_time->format('d M Y H:i'))
            ->line('**Alasan Penolakan:** ' . $this->note)
            ->action('Cek Jadwal Lain di Kalender', url('/reservations/calendar'))
            ->line('Silakan memilih jadwal atau ruangan lain yang tersedia.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reservasi Ditolak',
            'message' => "Reservasi Anda untuk {$this->reservation->room->name} ditolak. Alasan: {$this->note}",
            'reservation_id' => $this->reservation->id,
            'room_name' => $this->reservation->room->name,
            'note' => $this->note,
            'start_time' => $this->reservation->start_time->format('d M Y H:i'),
            'end_time' => $this->reservation->end_time->format('d M Y H:i'),
            'url' => '/reservations/' . $this->reservation->id,
            'type' => 'rejected',
        ];
    }
}

