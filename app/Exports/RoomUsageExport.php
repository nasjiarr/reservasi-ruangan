<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoomUsageExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?string $startDate = null,
        protected ?string $endDate = null,
        protected ?int $roomId = null,
    ) {}

    /**
     * Build the query for export.
     */
    public function query()
    {
        $query = Reservation::query()
            ->with(['room', 'user', 'checkIn'])
            ->latest('start_time');

        if ($this->startDate) {
            $query->whereDate('start_time', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('end_time', '<=', $this->endDate);
        }

        if ($this->roomId) {
            $query->where('room_id', $this->roomId);
        }

        return $query;
    }

    /**
     * Column headers.
     */
    public function headings(): array
    {
        return [
            'Nama Ruangan',
            'Judul Reservasi',
            'Pemesan',
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Status',
            'Waktu Check-in',
        ];
    }

    /**
     * Map each row data.
     *
     * @param Reservation $reservation
     */
    public function map($reservation): array
    {
        return [
            $reservation->room?->name ?? '-',
            $reservation->title,
            $reservation->user?->name ?? '-',
            $reservation->start_time->format('d/m/Y'),
            $reservation->start_time->format('H:i'),
            $reservation->end_time->format('H:i'),
            ucfirst($reservation->status),
            $reservation->checkIn?->checked_in_at
                ? $reservation->checkIn->checked_in_at->format('d/m/Y H:i')
                : 'Belum Check-in',
        ];
    }
}

