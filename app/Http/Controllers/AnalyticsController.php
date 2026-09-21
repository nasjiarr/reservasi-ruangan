<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-reports');
    }

    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request): Response
    {
        // 1. Rentang Tanggal Filter (Default 30 hari terakhir)
        $defaultStart = now()->subDays(29)->format('Y-m-d');
        $defaultEnd = now()->format('Y-m-d');

        $startDate = $request->query('start_date', $defaultStart);
        $endDate = $request->query('end_date', $defaultEnd);

        // Validasi jika format tanggal tertukar
        if ($startDate > $endDate) {
            $startDate = $defaultStart;
            $endDate = $defaultEnd;
        }

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Hitung periode sebelumnya untuk perbandingan persentase (% change)
        $durationInDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $prevEndDateTime = (clone $startDateTime)->subSecond();
        $prevStartDateTime = (clone $prevEndDateTime)->subDays($durationInDays - 1)->startOfDay();

        // 2. Summary KPI Cards
        // a. Total Reservasi
        $totalReservations = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])->count();
        $prevTotalReservations = Reservation::whereBetween('created_at', [$prevStartDateTime, $prevEndDateTime])->count();
        $totalReservationsChange = $prevTotalReservations > 0
            ? round((($totalReservations - $prevTotalReservations) / $prevTotalReservations) * 100, 1)
            : ($totalReservations > 0 ? 100.0 : 0.0);

        // b. Tingkat Approval Rate (approved / (approved + rejected) * 100)
        $approvedCount = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'approved')->count();
        $rejectedCount = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('status', 'rejected')->count();
        $totalDecided = $approvedCount + $rejectedCount;
        $approvalRate = $totalDecided > 0 ? round(($approvedCount / $totalDecided) * 100, 1) : 0.0;

        $prevApproved = Reservation::whereBetween('created_at', [$prevStartDateTime, $prevEndDateTime])
            ->where('status', 'approved')->count();
        $prevRejected = Reservation::whereBetween('created_at', [$prevStartDateTime, $prevEndDateTime])
            ->where('status', 'rejected')->count();
        $prevTotalDecided = $prevApproved + $prevRejected;
        $prevApprovalRate = $prevTotalDecided > 0 ? round(($prevApproved / $prevTotalDecided) * 100, 1) : 0.0;
        $approvalRateChange = round($approvalRate - $prevApprovalRate, 1);

        // c. Ruangan Paling Sering Dipakai
        $topRoomRecord = Reservation::join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->whereBetween('reservations.created_at', [$startDateTime, $endDateTime])
            ->whereIn('reservations.status', ['approved', 'pending'])
            ->select('rooms.name', DB::raw('COUNT(reservations.id) as booking_count'))
            ->groupBy('rooms.id', 'rooms.name')
            ->orderByDesc('booking_count')
            ->first();

        $mostUsedRoom = [
            'name' => $topRoomRecord ? $topRoomRecord->name : 'Belum Ada',
            'count' => $topRoomRecord ? (int) $topRoomRecord->booking_count : 0,
        ];

        // d. Rata-rata Durasi Meeting (dalam menit)
        $avgDurationMinutes = (float) (Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_dur')
            ->value('avg_dur') ?? 0);
        $avgDurationMinutes = round($avgDurationMinutes);

        $prevAvgDurationMinutes = (float) (Reservation::whereBetween('created_at', [$prevStartDateTime, $prevEndDateTime])
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_dur')
            ->value('avg_dur') ?? 0);
        $prevAvgDurationMinutes = round($prevAvgDurationMinutes);

        $avgDurationChange = $prevAvgDurationMinutes > 0
            ? round((($avgDurationMinutes - $prevAvgDurationMinutes) / $prevAvgDurationMinutes) * 100, 1)
            : ($avgDurationMinutes > 0 ? 100.0 : 0.0);

        // 3. Line Chart Data: Tren Reservasi Harian
        $dailyBookingsRaw = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $trendLabels = [];
        $trendValues = [];
        $cursor = Carbon::parse($startDate);
        $endCursor = Carbon::parse($endDate);

        while ($cursor->lte($endCursor)) {
            $formattedDate = $cursor->format('Y-m-d');
            $trendLabels[] = $cursor->translatedFormat('d M');
            $trendValues[] = (int) ($dailyBookingsRaw[$formattedDate] ?? 0);
            $cursor->addDay();
        }

        // 4. Bar Chart Data: Top 5 Ruangan Paling Sering Dibooking
        $top5Rooms = Reservation::join('rooms', 'reservations.room_id', '=', 'rooms.id')
            ->whereBetween('reservations.created_at', [$startDateTime, $endDateTime])
            ->select('rooms.name', DB::raw('COUNT(reservations.id) as total'))
            ->groupBy('rooms.id', 'rooms.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $topRoomsLabels = $top5Rooms->pluck('name')->toArray();
        $topRoomsValues = $top5Rooms->pluck('total')->map(fn ($val) => (int) $val)->toArray();

        // 5. Doughnut Chart Data: Distribusi Status Reservasi
        $statusCountsRaw = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusList = ['pending', 'approved', 'rejected', 'cancelled'];
        $statusLabelsMap = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];
        $statusColorsMap = [
            'pending' => '#F59E0B',   // Warm Amber
            'approved' => '#10B981',  // Emerald
            'rejected' => '#F43F5E',  // Rose
            'cancelled' => '#94A3B8', // Slate
        ];

        $statusDistribution = [];
        $statusChartLabels = [];
        $statusChartValues = [];
        $statusChartColors = [];
        $totalStatusSum = array_sum($statusCountsRaw);

        foreach ($statusList as $st) {
            $count = (int) ($statusCountsRaw[$st] ?? 0);
            $pct = $totalStatusSum > 0 ? round(($count / $totalStatusSum) * 100, 1) : 0.0;

            $statusChartLabels[] = $statusLabelsMap[$st];
            $statusChartValues[] = $count;
            $statusChartColors[] = $statusColorsMap[$st];

            $statusDistribution[] = [
                'status' => $st,
                'label' => $statusLabelsMap[$st],
                'count' => $count,
                'percentage' => $pct,
                'color' => $statusColorsMap[$st],
            ];
        }

        // 6. Horizontal Bar Chart Data: Jam Paling Sibuk (08:00 - 18:00)
        $busyHoursRaw = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereRaw('HOUR(start_time) BETWEEN 8 AND 18')
            ->selectRaw('HOUR(start_time) as hr, COUNT(*) as count')
            ->groupBy('hr')
            ->pluck('count', 'hr')
            ->toArray();

        $busiestHoursLabels = [];
        $busiestHoursValues = [];
        for ($h = 8; $h <= 18; $h++) {
            $busiestHoursLabels[] = sprintf('%02d:00', $h);
            $busiestHoursValues[] = (int) ($busyHoursRaw[$h] ?? 0);
        }

        // 7. Heatmap Data: Hari dalam Seminggu vs Jam (08:00 - 18:00)
        // WEEKDAY() di MySQL: 0 = Senin, 1 = Selasa, ..., 6 = Minggu
        $heatmapRaw = Reservation::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereRaw('HOUR(start_time) BETWEEN 8 AND 18')
            ->selectRaw('WEEKDAY(start_time) as dow, HOUR(start_time) as hr, COUNT(*) as count')
            ->groupBy('dow', 'hr')
            ->get();

        $heatmapLookup = [];
        $maxHeatmapCount = 0;
        foreach ($heatmapRaw as $row) {
            $heatmapLookup[(int) $row->dow][(int) $row->hr] = (int) $row->count;
            if ((int) $row->count > $maxHeatmapCount) {
                $maxHeatmapCount = (int) $row->count;
            }
        }

        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $heatmapMatrix = [];
        for ($d = 0; $d < 7; $d++) {
            $hoursGrid = [];
            for ($h = 8; $h <= 18; $h++) {
                $cellCount = $heatmapLookup[$d][$h] ?? 0;
                $hoursGrid[] = [
                    'hour' => sprintf('%02d:00', $h),
                    'count' => $cellCount,
                ];
            }
            $heatmapMatrix[] = [
                'day' => $dayNames[$d],
                'day_index' => $d,
                'hours' => $hoursGrid,
            ];
        }

        return Inertia::render('Analytics/Index', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_reservations' => [
                    'value' => $totalReservations,
                    'change' => $totalReservationsChange,
                ],
                'approval_rate' => [
                    'value' => $approvalRate,
                    'change' => $approvalRateChange,
                ],
                'most_used_room' => $mostUsedRoom,
                'avg_duration' => [
                    'value' => $avgDurationMinutes,
                    'change' => $avgDurationChange,
                ],
            ],
            'lineChart' => [
                'labels' => $trendLabels,
                'datasets' => [
                    [
                        'label' => 'Total Reservasi',
                        'data' => $trendValues,
                        'borderColor' => '#0D9488', // brand-600 Nordic Teal
                        'backgroundColor' => 'rgba(13, 148, 136, 0.12)',
                        'borderWidth' => 2.5,
                        'tension' => 0.35,
                        'fill' => true,
                        'pointBackgroundColor' => '#0D9488',
                        'pointRadius' => 3,
                        'pointHoverRadius' => 6,
                    ],
                ],
            ],
            'barChartTopRooms' => [
                'labels' => $topRoomsLabels,
                'datasets' => [
                    [
                        'label' => 'Jumlah Pemesanan',
                        'data' => $topRoomsValues,
                        'backgroundColor' => '#0D9488',
                        'borderRadius' => 6,
                        'hoverBackgroundColor' => '#0F766E',
                    ],
                ],
            ],
            'doughnutStatus' => [
                'labels' => $statusChartLabels,
                'datasets' => [
                    [
                        'data' => $statusChartValues,
                        'backgroundColor' => $statusChartColors,
                        'borderWidth' => 2,
                        'borderColor' => '#FFFFFF',
                        'hoverOffset' => 4,
                    ],
                ],
                'distribution' => $statusDistribution,
                'total' => $totalStatusSum,
            ],
            'horizontalBarHours' => [
                'labels' => $busiestHoursLabels,
                'datasets' => [
                    [
                        'label' => 'Pemesanan',
                        'data' => $busiestHoursValues,
                        'backgroundColor' => '#334155', // slate-700
                        'borderRadius' => 4,
                        'hoverBackgroundColor' => '#0D9488',
                    ],
                ],
            ],
            'heatmap' => [
                'matrix' => $heatmapMatrix,
                'max_count' => $maxHeatmapCount,
            ],
        ]);
    }
}

