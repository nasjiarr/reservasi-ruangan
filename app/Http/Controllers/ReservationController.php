<?php

namespace App\Http\Controllers;

use App\Events\ReservationStatusChanged;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Notifications\ReservationCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Reservation::with(['room', 'user', 'checkIn'])
            ->latest('start_time');

        // Admin dan Manager yang punya izin view-all-reservations bisa melihat semua
        if (!$user->can('view-all-reservations')) {
            $query->where('user_id', $user->id);
        }

        $reservations = $query->paginate(10)->withQueryString();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Display the reservation calendar view.
     */
    public function calendar(): Response
    {
        $reservations = Reservation::with(['room', 'user'])
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->map(function ($res) {
                $color = match ($res->status) {
                    'approved' => '#10B981', // green-500
                    'pending' => '#F59E0B',  // amber-500
                    'rejected' => '#EF4444', // red-500
                    'cancelled' => '#6B7280',// gray-500
                    default => '#3B82F6',
                };

                return [
                    'id' => (string) $res->id,
                    'title' => $res->room->name . ' - ' . $res->title,
                    'start' => $res->start_time->toIso8601String(),
                    'end' => $res->end_time->toIso8601String(),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'room_name' => $res->room->name,
                        'user_name' => $res->user->name,
                        'title' => $res->title,
                        'description' => $res->description,
                        'status' => $res->status,
                        'start_formatted' => $res->start_time->format('d M Y H:i'),
                        'end_formatted' => $res->end_time->format('d M Y H:i'),
                    ],
                ];
            });

        return Inertia::render('Reservations/Calendar', [
            'events' => $reservations,
        ]);
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create(): Response
    {
        $rooms = Room::where('status', 'active')
            ->with('facilities')
            ->get();

        return Inertia::render('Reservations/Create', [
            'rooms' => $rooms,
        ]);
    }

    /**
     * Store a newly created reservation in storage.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $reservation = Reservation::create($validated);
        $reservation->load(['room', 'user']);

        // Kirim notifikasi ke semua user yang memiliki permission 'approve-reservation'
        $approvers = User::permission('approve-reservation')->get();
        if ($approvers->isNotEmpty()) {
            Notification::send($approvers, new ReservationCreatedNotification($reservation));
        }

        // Broadcast event status changed
        event(new ReservationStatusChanged($reservation));

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil diajukan dan menunggu persetujuan.');
    }

    /**
     * Display the specified reservation.
     */
    public function show(Request $request, Reservation $reservation): Response
    {
        $user = $request->user();

        // Otorisasi: hanya pemilik atau user berizin view-all-reservations yang boleh melihat
        if (!$user->can('view-all-reservations') && $reservation->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat reservasi ini.');
        }

        $reservation->load(['room.facilities', 'user', 'approvals.approver', 'checkIn']);

        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Cancel the specified reservation (soft cancellation).
     */
    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = $request->user();

        // Otorisasi: hanya pemilik atau admin yang boleh membatalkan
        if (!$user->hasRole('admin') && $reservation->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk membatalkan reservasi ini.');
        }

        // Jangan hard delete, ubah status menjadi cancelled
        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil dibatalkan.');
    }
}

