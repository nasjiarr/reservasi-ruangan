<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the operational command center dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $today = Carbon::today();

        // 1. Time-based dynamic greeting
        $hour = $now->hour;
        if ($hour >= 5 && $hour < 11) {
            $greeting = 'Selamat Pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 19) {
            $greeting = 'Selamat Sore';
        } else {
            $greeting = 'Selamat Malam';
        }

        // Indonesian date format
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $todayFormatted = $days[$now->dayOfWeek] . ', ' . $now->format('j') . ' ' . $months[$now->month] . ' ' . $now->format('Y');

        $canApprove = $user->can('approve-reservation') || $user->hasRole(['admin', 'manager']);

        // 2. Operational metrics (Today & Real-time)
        $todayReservationsCount = Reservation::whereDate('start_time', $today)->count();

        $occupiedRoomIds = Reservation::where('status', 'approved')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->pluck('room_id')
            ->unique();

        $roomsOccupiedNow = Room::where('status', 'active')
            ->whereIn('id', $occupiedRoomIds)
            ->count();

        $totalActiveRooms = Room::where('status', 'active')->count();

        $pendingApprovalsCount = Reservation::where('status', 'pending')->count();
        $userUpcomingCount = Reservation::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_time', '>=', $now)
            ->count();

        $checkinsTodayCount = CheckIn::whereNotNull('checked_in_at')
            ->whereDate('checked_in_at', $today)
            ->count();

        $stats = [
            'today_reservations' => $todayReservationsCount,
            'rooms_occupied_now' => $roomsOccupiedNow,
            'total_active_rooms' => $totalActiveRooms,
            'pending_approvals' => $pendingApprovalsCount,
            'user_upcoming_reservations' => $userUpcomingCount,
            'checkins_today' => $checkinsTodayCount,
            'can_approve' => $canApprove,
        ];

        // 3. User's Next Upcoming Meeting
        $nextMeetingRaw = Reservation::with(['room', 'checkIn'])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('end_time', '>=', $now)
            ->orderBy('start_time', 'asc')
            ->first();

        $nextMeeting = null;
        if ($nextMeetingRaw) {
            $isHappeningNow = $now->between($nextMeetingRaw->start_time, $nextMeetingRaw->end_time);
            $nextMeeting = [
                'id' => $nextMeetingRaw->id,
                'title' => $nextMeetingRaw->title,
                'description' => $nextMeetingRaw->description,
                'room_name' => $nextMeetingRaw->room?->name,
                'room_location' => $nextMeetingRaw->room?->location,
                'start_time' => $nextMeetingRaw->start_time->format('H:i'),
                'end_time' => $nextMeetingRaw->end_time->format('H:i'),
                'date_formatted' => $nextMeetingRaw->start_time->format('d M Y'),
                'is_today' => $nextMeetingRaw->start_time->isToday(),
                'is_happening_now' => $isHappeningNow,
                'time_text' => $isHappeningNow ? 'Sedang berlangsung' : $nextMeetingRaw->start_time->diffForHumans(),
                'qr_token' => $nextMeetingRaw->checkIn?->qr_token,
                'is_checked_in' => !is_null($nextMeetingRaw->checkIn?->checked_in_at),
            ];
        }

        // 4. Live Room Status Grid (All active rooms)
        $roomStatuses = Room::where('status', '!=', 'inactive')
            ->with(['reservations' => function ($q) use ($now) {
                $q->where('status', 'approved')
                    ->where('start_time', '<=', $now)
                    ->where('end_time', '>=', $now)
                    ->with('user:id,name');
            }])
            ->get()
            ->map(function ($room) {
                $currentReservation = $room->reservations->first();
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'location' => $room->location,
                    'capacity' => $room->capacity,
                    'status' => $room->status, // 'active' or 'maintenance'
                    'is_occupied' => !is_null($currentReservation),
                    'current_booking' => $currentReservation ? [
                        'title' => $currentReservation->title,
                        'user_name' => $currentReservation->user?->name,
                        'end_time' => $currentReservation->end_time->format('H:i'),
                    ] : null,
                ];
            });

        // 5. Recent Reservations / Approval Queue
        if ($canApprove) {
            $recentReservations = Reservation::with(['user:id,name,email', 'room:id,name,location'])
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->latest()
                ->take(6)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'room_name' => $r->room?->name ?? 'Ruangan Dihapus',
                    'user_name' => $r->user?->name ?? 'User Dihapus',
                    'user_email' => $r->user?->email,
                    'date' => $r->start_time->format('d M Y'),
                    'start_time' => $r->start_time->format('H:i'),
                    'end_time' => $r->end_time->format('H:i'),
                    'status' => $r->status,
                ]);
        } else {
            $recentReservations = Reservation::with(['room:id,name,location'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(6)
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                    'room_name' => $r->room?->name ?? 'Ruangan Dihapus',
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'date' => $r->start_time->format('d M Y'),
                    'start_time' => $r->start_time->format('H:i'),
                    'end_time' => $r->end_time->format('H:i'),
                    'status' => $r->status,
                ]);
        }

        return Inertia::render('Dashboard', [
            'greeting' => $greeting,
            'todayDate' => $todayFormatted,
            'stats' => $stats,
            'nextMeeting' => $nextMeeting,
            'roomStatuses' => $roomStatuses,
            'recentReservations' => $recentReservations,
        ]);
    }
}

