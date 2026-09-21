<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();
        $users = User::all();

        if ($rooms->isEmpty() || $users->isEmpty()) {
            return;
        }

        $titles = [
            'Rapat Koordinasi Manajemen',
            'Weekly Sprint Planning & Review',
            'Sesi Brainstorming Produk Baru',
            'Interview Kandidat Lead Developer',
            'Town Hall & Evaluasi Kinerja Kuartal',
            'Workshop Desain UI/UX & Design System',
            'Presentasi Pitching Klien Strategis',
            'Sync Up Divisi Operasional & Logistik',
            'Training Keamanan Sistem & DevSecOps',
            'Rapat Pembahasan Anggaran Tahunan',
            'Diskusi Internal Tim Pemasaran Digital',
            'Vendor Alignment & Kontrak Pengadaan',
        ];

        $statuses = ['approved', 'approved', 'approved', 'approved', 'pending', 'pending', 'rejected', 'cancelled'];

        // Buat 45 reservasi simulasi tersebar dalam 28 hari terakhir
        for ($i = 0; $i < 45; $i++) {
            $daysAgo = rand(0, 27);
            $hour = rand(8, 16);
            $durationHours = rand(1, 3);

            $date = Carbon::now()->subDays($daysAgo)->setTime($hour, 0, 0);
            $startTime = (clone $date);
            $endTime = (clone $date)->addHours($durationHours);
            $createdAt = (clone $startTime)->subDays(rand(1, 3))->setTime(rand(8, 17), rand(0, 59));

            $room = $rooms->random();
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $title = $titles[array_rand($titles)];

            Reservation::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'title' => $title,
                'description' => "Agenda pembahasan: {$title}. Harap peserta hadir tepat waktu.",
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
                'is_recurring' => false,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}

