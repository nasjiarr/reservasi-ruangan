<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RoomAvailableRule implements ValidationRule
{
    public function __construct(
        protected int $roomId,
        protected string $startTime,
        protected ?int $excludeReservationId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->roomId) || empty($this->startTime)) {
            return;
        }

        $endTime = $value;

        $query = Reservation::where('room_id', $this->roomId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($endTime) {
                $query->whereBetween('start_time', [$this->startTime, $endTime])
                    ->orWhereBetween('end_time', [$this->startTime, $endTime])
                    ->orWhere(function ($q) use ($endTime) {
                        $q->where('start_time', '<=', $this->startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            });

        if ($this->excludeReservationId) {
            $query->where('id', '!=', $this->excludeReservationId);
        }

        if ($query->exists()) {
            $fail('Ruangan sudah dibooking pada rentang waktu tersebut. Silakan pilih waktu atau ruangan lain.');
        }
    }
}

