<?php

namespace App\Http\Controllers;

use App\Exports\RoomUsageExport;
use App\Models\Reservation;
use App\Models\Room;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-reports');
    }

    /**
     * Display the report filter page with analytics and preview.
     */
    public function index(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $roomId = $request->query('room_id');

        // 1. Ringkasan Analytics
        $totalThisMonth = Reservation::whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year)
            ->count();

        // Top 3 ruangan paling sering dipesan
        $topRooms = Room::withCount(['reservations' => function ($q) {
                $q->whereIn('status', ['approved', 'pending']);
            }])
            ->orderByDesc('reservations_count')
            ->take(3)
            ->get(['id', 'name', 'location', 'capacity']);

        // 2. Data Reservasi Terfilter untuk Preview Tabel
        $query = Reservation::with(['room', 'user', 'checkIn'])
            ->latest('start_time');

        if ($startDate) {
            $query->whereDate('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('end_time', '<=', $endDate);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        $reservations = $query->paginate(15)->withQueryString();
        $rooms = Room::all(['id', 'name']);

        return Inertia::render('Reports/Index', [
            'reservations' => $reservations,
            'rooms' => $rooms,
            'filters' => [
                'start_date' => $startDate ?? '',
                'end_date' => $endDate ?? '',
                'room_id' => $roomId ?? '',
            ],
            'analytics' => [
                'total_this_month' => $totalThisMonth,
                'top_rooms' => $topRooms,
            ],
        ]);
    }

    /**
     * Export reservations to Excel file.
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $roomId = $request->query('room_id') ? (int) $request->query('room_id') : null;

        $fileName = 'laporan-reservasi-' . date('Ymd-His') . '.xlsx';

        return Excel::download(
            new RoomUsageExport($startDate, $endDate, $roomId),
            $fileName
        );
    }

    /**
     * Export reservations to PDF file using DomPDF.
     */
    public function exportPdf(Request $request): HttpResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $roomId = $request->query('room_id') ? (int) $request->query('room_id') : null;

        $query = Reservation::with(['room', 'user', 'checkIn'])
            ->latest('start_time');

        if ($startDate) {
            $query->whereDate('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('end_time', '<=', $endDate);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        $reservations = $query->get();
        $room = $roomId ? Room::find($roomId) : null;

        $pdf = Pdf::loadView('reports.room-usage', [
            'reservations' => $reservations,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'room' => $room,
            'generatedAt' => now()->format('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-reservasi-' . date('Ymd-His') . '.pdf');
    }
}

