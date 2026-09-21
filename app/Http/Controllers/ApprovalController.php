<?php

namespace App\Http\Controllers;

use App\Events\ReservationStatusChanged;
use App\Models\Reservation;
use App\Notifications\ReservationApprovedNotification;
use App\Notifications\ReservationRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:approve-reservation');
    }

    /**
     * Display a listing of pending or reviewed reservations for approval management.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            $status = 'pending';
        }

        $reservations = Reservation::with(['room', 'user', 'checkIn'])
            ->where('status', $status)
            ->latest('start_time')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Approvals/Index', [
            'reservations' => $reservations,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Approve the specified reservation.
     */
    public function approve(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya reservasi berstatus pending yang dapat disetujui.');
        }

        DB::transaction(function () use ($request, $reservation) {
            $reservation->update(['status' => 'approved']);

            $reservation->approvals()->create([
                'approver_id' => $request->user()->id,
                'status' => 'approved',
                'note' => $request->input('note'),
                'level' => 1,
            ]);

            // Buat record CheckIn baru dengan token unik
            if (!$reservation->checkIn) {
                $reservation->checkIn()->create([
                    'qr_token' => (string) \Illuminate\Support\Str::uuid(),
                    'checked_in_at' => null,
                ]);
            }
        });

        $reservation->load(['room', 'user', 'checkIn']);

        // Kirim notifikasi persetujuan ke pemesan
        $reservation->user->notify(new ReservationApprovedNotification($reservation, $request->user()));

        // Broadcast perubahan status ke FullCalendar (graceful fallback jika Reverb offline)
        rescue(fn () => event(new ReservationStatusChanged($reservation)), function ($e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed (Reverb offline): ' . $e->getMessage());
        }, false);

        return redirect()->back()->with('success', 'Reservasi berhasil disetujui.');
    }

    /**
     * Reject the specified reservation.
     */
    public function reject(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya reservasi berstatus pending yang dapat ditolak.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $reservation, $validated) {
            $reservation->update(['status' => 'rejected']);

            $reservation->approvals()->create([
                'approver_id' => $request->user()->id,
                'status' => 'rejected',
                'note' => $validated['note'],
                'level' => 1,
            ]);
        });

        $reservation->load(['room', 'user']);

        // Kirim notifikasi penolakan ke pemesan
        $reservation->user->notify(new ReservationRejectedNotification($reservation, $validated['note']));

        // Broadcast perubahan status ke FullCalendar (graceful fallback jika Reverb offline)
        rescue(fn () => event(new ReservationStatusChanged($reservation)), function ($e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed (Reverb offline): ' . $e->getMessage());
        }, false);

        return redirect()->back()->with('success', 'Reservasi berhasil ditolak.');
    }
}

