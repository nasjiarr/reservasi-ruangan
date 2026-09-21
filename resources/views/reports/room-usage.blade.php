<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penggunaan Ruangan</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #1f2937;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #6b7280;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-approved { background-color: #d1fae5; color: #065f46; }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-rejected { background-color: #fee2e2; color: #991b1b; }
        .badge-cancelled { background-color: #f3f4f6; color: #4b5563; }
        .footer {
            margin-top: 30px;
            width: 100%;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Penggunaan & Reservasi Ruangan</h1>
        <p>Sistem Informasi Manajemen Reservasi Ruangan</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Periode:</strong></td>
            <td style="width: 35%;">
                @if($startDate && $endDate)
                    {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @elseif($startDate)
                    Mulai {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                @elseif($endDate)
                    Sampai {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @else
                    Semua Waktu
                @endif
            </td>
            <td style="width: 15%;"><strong>Filter Ruangan:</strong></td>
            <td style="width: 35%;">{{ $room ? $room->name : 'Semua Ruangan' }}</td>
        </tr>
        <tr>
            <td><strong>Dicetak Pada:</strong></td>
            <td>{{ $generatedAt }}</td>
            <td><strong>Total Data:</strong></td>
            <td>{{ count($reservations) }} Reservasi</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 16%;">Nama Ruangan</th>
                <th style="width: 20%;">Judul Reservasi</th>
                <th style="width: 15%;">Pemesan</th>
                <th style="width: 10%; text-align: center;">Tanggal</th>
                <th style="width: 10%; text-align: center;">Waktu</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 14%; text-align: center;">Check-In</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $index => $res)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $res->room?->name ?? '-' }}</strong></td>
                    <td>{{ $res->title }}</td>
                    <td>{{ $res->user?->name ?? '-' }}</td>
                    <td class="text-center">{{ $res->start_time->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $res->start_time->format('H:i') }} - {{ $res->end_time->format('H:i') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $res->status }}">
                            {{ ucfirst($res->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($res->checkIn && $res->checkIn->checked_in_at)
                            {{ $res->checkIn->checked_in_at->format('d/m/Y H:i') }}
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #6b7280;">
                        Tidak ada data reservasi untuk filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td style="width: 70%;"></td>
            <td class="text-center" style="width: 30%;">
                <p>Dicetak secara otomatis oleh sistem</p>
                <div style="height: 50px;"></div>
                <p><strong>Bagian Administrasi Umum</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>

