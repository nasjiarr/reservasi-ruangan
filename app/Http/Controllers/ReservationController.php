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
                $styles = match ($res->status) {
                    'approved' => [
                        'bg' => '#ECFDF5',
                        'border' => '#10B981',
                        'text' => '#065F46',
                    ],
                    'pending' => [
                        'bg' => '#FFFBEB',
                        'border' => '#F59E0B',
                        'text' => '#92400E',
                    ],
                    'rejected' => [
                        'bg' => '#FEF2F2',
                        'border' => '#EF4444',
                        'text' => '#991B1B',
                    ],
                    'cancelled' => [
                        'bg' => '#F3F4F6',
                        'border' => '#9CA3AF',
                        'text' => '#374151',
                    ],
                    default => [
                        'bg' => '#EFF6FF',
                        'border' => '#3B82F6',
                        'text' => '#1E40AF',
                    ],
                };

                return [
                    'id' => (string) $res->id,
                    'title' => $res->title,
                    'start' => $res->start_time->toIso8601String(),
                    'end' => $res->end_time->toIso8601String(),
                    'backgroundColor' => $styles['bg'],
                    'borderColor' => $styles['border'],
                    'textColor' => $styles['text'],
                    'extendedProps' => [
                        'room_id' => $res->room_id,
                        'room_name' => $res->room?->name ?? 'Ruangan',
                        'user_name' => $res->user?->name ?? 'Pemesan',
                        'title' => $res->title,
                        'description' => $res->description,
                        'status' => $res->status,
                        'start_time' => $res->start_time->format('H:i'),
                        'end_time' => $res->end_time->format('H:i'),
                        'start_formatted' => $res->start_time->format('d M Y H:i'),
                        'end_formatted' => $res->end_time->format('d M Y H:i'),
                    ],
                ];
            });

        $rooms = Room::where('status', 'active')
            ->select('id', 'name', 'location')
            ->orderBy('name')
            ->get();

        return Inertia::render('Reservations/Calendar', [
            'events' => $reservations,
            'rooms' => $rooms,
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

        // Broadcast event status changed (graceful fallback jika Reverb offline)
        rescue(fn () => event(new ReservationStatusChanged($reservation)), function ($e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed (Reverb offline): ' . $e->getMessage());
        }, false);

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
            'breadcrumbOverride' => $reservation->title,
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

