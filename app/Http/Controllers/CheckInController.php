<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CheckInController extends Controller
{
    /**
     * Display the public check-in confirmation page for a scanned QR code.
     */
    public function show(string $token): Response
    {
        $checkIn = CheckIn::where('qr_token', $token)
            ->with(['reservation.room', 'reservation.user'])
            ->first();

        if (!$checkIn || !$checkIn->reservation) {
            return Inertia::render('CheckIn/Show', [
                'isValid' => false,
                'status' => 'invalid',
                'statusMessage' => 'Token check-in tidak valid atau data reservasi tidak ditemukan.',
                'canCheckIn' => false,
                'reservation' => null,
                'checkIn' => null,
            ]);
        }

        $reservation = $checkIn->reservation;
        $now = now();
        $startTime = Carbon::parse($reservation->start_time);
        $endTime = Carbon::parse($reservation->end_time);
        $checkInAllowedFrom = $startTime->copy()->subMinutes(15);

        $status = 'ready';
        $statusMessage = 'Silakan konfirmasi kehadiran Anda.';
        $canCheckIn = true;

        if ($reservation->status !== 'approved') {
            $status = 'not_approved';
            $statusMessage = 'Reservasi ini belum atau tidak disetujui (Status: ' . ucfirst($reservation->status) . ').';
            $canCheckIn = false;
        } elseif ($checkIn->checked_in_at !== null) {
            $status = 'already_checked_in';
            $statusMessage = 'Sudah check-in sebelumnya pada ' . $checkIn->checked_in_at->format('d M Y H:i:s') . '.';
            $canCheckIn = false;
        } elseif ($now->lt($checkInAllowedFrom)) {
            $status = 'too_early';
            $statusMessage = 'Belum waktunya check-in. Check-in dapat dilakukan mulai 15 menit sebelum kegiatan (' . $checkInAllowedFrom->format('H:i') . ' WIB).';
            $canCheckIn = false;
        } elseif ($now->gt($endTime)) {
            $status = 'expired';
            $statusMessage = 'Sesi sudah berakhir pada ' . $endTime->format('d M Y H:i') . '.';
            $canCheckIn = false;
        }

        return Inertia::render('CheckIn/Show', [
            'isValid' => true,
            'status' => $status,
            'statusMessage' => $statusMessage,
            'canCheckIn' => $canCheckIn,
            'reservation' => [
                'id' => $reservation->id,
                'title' => $reservation->title,
                'description' => $reservation->description,
                'room_name' => $reservation->room?->name ?? '-',
                'room_location' => $reservation->room?->location ?? '-',
                'user_name' => $reservation->user?->name ?? '-',
                'start_time' => $startTime->format('d M Y, H:i'),
                'end_time' => $endTime->format('d M Y, H:i'),
            ],
            'checkIn' => [
                'qr_token' => $checkIn->qr_token,
                'checked_in_at' => $checkIn->checked_in_at ? $checkIn->checked_in_at->format('d M Y H:i:s') : null,
            ],
        ]);
    }

    /**
     * Process the check-in confirmation.
     */
    public function confirm(Request $request, string $token): RedirectResponse
    {
        $checkIn = CheckIn::where('qr_token', $token)
            ->with('reservation')
            ->first();

        if (!$checkIn || !$checkIn->reservation) {
            return redirect()->back()->with('error', 'Token check-in tidak valid.');
        }

        $reservation = $checkIn->reservation;
        $now = now();
        $startTime = Carbon::parse($reservation->start_time);
        $endTime = Carbon::parse($reservation->end_time);
        $checkInAllowedFrom = $startTime->copy()->subMinutes(15);

        if ($reservation->status !== 'approved') {
            return redirect()->back()->with('error', 'Reservasi tidak dalam status disetujui.');
        }

        if ($checkIn->checked_in_at !== null) {
            return redirect()->back()->with('error', 'Sudah check-in sebelumnya pada ' . $checkIn->checked_in_at->format('d M Y H:i:s') . '.');
        }

        if ($now->lt($checkInAllowedFrom)) {
            return redirect()->back()->with('error', 'Belum waktunya check-in. Silakan tunggu hingga 15 menit sebelum acara.');
        }

        if ($now->gt($endTime)) {
            return redirect()->back()->with('error', 'Sesi sudah berakhir. Anda tidak dapat melakukan check-in.');
        }

        $checkIn->update([
            'checked_in_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Konfirmasi kehadiran berhasil! Selamat berkegiatan di ' . ($reservation->room?->name ?? 'ruangan') . '.');
    }

    /**
     * Generate QR Code image for the given reservation.
     */
    public function qrCode(Reservation $reservation): HttpResponse
    {
        // Pastikan hanya pemilik atau approver yang dapat mengunduh/melihat QR code
        $user = auth()->user();
        if (!$user->can('approve-reservation') && $reservation->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke QR Code ini.');
        }

        if ($reservation->status !== 'approved') {
            abort(400, 'QR Code hanya tersedia untuk reservasi yang berstatus disetujui (approved).');
        }

        // Pastikan record CheckIn tersedia
        $checkIn = $reservation->checkIn;
        if (!$checkIn) {
            $checkIn = $reservation->checkIn()->create([
                'qr_token' => (string) Str::uuid(),
                'checked_in_at' => null,
            ]);
        }

        $url = url('/check-in/' . $checkIn->qr_token);

        if (extension_loaded('imagick')) {
            $qr = QrCode::format('png')->size(300)->margin(1)->generate($url);
            return response($qr, 200, ['Content-Type' => 'image/png']);
        }

        $qr = QrCode::format('svg')->size(300)->margin(1)->generate($url);
        return response($qr, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
