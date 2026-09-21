<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_id',
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'status',
        'is_recurring',
        'recurrence_rule',
        'parent_reservation_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the room associated with the reservation.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the user who made the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all approvals for the reservation.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    /**
     * Get the check-in record for the reservation.
     */
    public function checkIn(): HasOne
    {
        return $this->hasOne(CheckIn::class);
    }

    /**
     * Get all check-in records for the reservation.
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Get the parent reservation (if this is a recurring instance).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'parent_reservation_id');
    }

    /**
     * Get all recurring reservations spawned by this parent reservation.
     */
    public function recurrences(): HasMany
    {
        return $this->hasMany(Reservation::class, 'parent_reservation_id');
    }

    /**
     * Scope a query to only include active reservations (pending or approved).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Scope a query to filter reservations for a specific room.
     */
    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope a query to find reservations that overlap with a given time range.
     * Overlap condition: start_time < $endTime AND end_time > $startTime
     */
    public function scopeOverlapping(Builder $query, mixed $startTime, mixed $endTime): Builder
    {
        return $query->where(function (Builder $q) use ($startTime, $endTime) {
            $q->where('start_time', '<', $endTime)
              ->where('end_time', '>', $startTime);
        });
    }
}
