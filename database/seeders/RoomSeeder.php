<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fasilitas
        $fProyektor = Facility::firstOrCreate(['name' => 'Proyektor 4K']);
        $fSound = Facility::firstOrCreate(['name' => 'Sound System & Mic']);
        $fWifi = Facility::firstOrCreate(['name' => 'High-Speed Wi-Fi']);
        $fAc = Facility::firstOrCreate(['name' => 'AC Sentral']);
        $fWhiteboard = Facility::firstOrCreate(['name' => 'Smart Whiteboard']);
        $fVideoConf = Facility::firstOrCreate(['name' => 'Video Conference System']);

        // Ruangan 1: Ruang Eksekutif
        $r1 = Room::updateOrCreate(
            ['name' => 'Ruang Rapat Eksekutif'],
            [
                'location' => 'Gedung A, Lantai 2',
                'capacity' => 20,
                'description' => 'Ruang rapat VIP dengan meja oval formal, dilengkapi layar proyektor 4K dan sistem konferensi video terintegrasi.',
                'status' => 'active',
            ]
        );
        $r1->facilities()->sync([$fProyektor->id, $fVideoConf->id, $fAc->id, $fWifi->id]);

        // Ruangan 2: Auditorium Utama
        $r2 = Room::updateOrCreate(
            ['name' => 'Auditorium Graha Utama'],
            [
                'location' => 'Gedung Utama, Lantai 1',
                'capacity' => 150,
                'description' => 'Auditorium berkapasitas besar cocok untuk seminar nasional, workshop perusahaan, dan town hall meeting.',
                'status' => 'active',
            ]
        );
        $r2->facilities()->sync([$fProyektor->id, $fSound->id, $fAc->id, $fWifi->id]);

        // Ruangan 3: Ruang Kolaborasi Kreatif
        $r3 = Room::updateOrCreate(
            ['name' => 'Creative Collaboration Room'],
            [
                'location' => 'Gedung B, Lantai 3',
                'capacity' => 12,
                'description' => 'Ruang santai dengan modular desk dan smart whiteboard untuk brainstorming tim produk & desain.',
                'status' => 'active',
            ]
        );
        $r3->facilities()->sync([$fWhiteboard->id, $fWifi->id, $fAc->id]);

        // Ruangan 4: Focus Discussion Room
        $r4 = Room::updateOrCreate(
            ['name' => 'Focus Discussion Room 1'],
            [
                'location' => 'Gedung A, Lantai 1',
                'capacity' => 8,
                'description' => 'Ruang diskusi intensif kedap suara untuk sesi interview dan meeting koordinasi harian.',
                'status' => 'active',
            ]
        );
        $r4->facilities()->sync([$fWhiteboard->id, $fWifi->id]);
    }
}

