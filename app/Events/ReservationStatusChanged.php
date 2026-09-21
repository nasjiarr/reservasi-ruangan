<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {
        $this->reservation->loadMissing(['room', 'user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('reservations'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ReservationStatusChanged';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $color = match ($this->reservation->status) {
            'approved' => '#10B981', // green-500
            'pending' => '#F59E0B',  // amber-500
            'rejected' => '#EF4444', // red-500
            'cancelled' => '#6B7280',// gray-500
            default => '#3B82F6',
        };

        return [
            'id' => (string) $this->reservation->id,
            'room_id' => $this->reservation->room_id,
            'status' => $this->reservation->status,
            'title' => ($this->reservation->room?->name ?? 'Ruangan') . ' - ' . $this->reservation->title,
            'start' => $this->reservation->start_time->toIso8601String(),
            'end' => $this->reservation->end_time->toIso8601String(),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'room_name' => $this->reservation->room?->name ?? '-',
                'user_name' => $this->reservation->user?->name ?? '-',
                'title' => $this->reservation->title,
                'description' => $this->reservation->description,
                'status' => $this->reservation->status,
                'start_formatted' => $this->reservation->start_time->format('d M Y H:i'),
                'end_formatted' => $this->reservation->end_time->format('d M Y H:i'),
            ],
        ];
    }
}

