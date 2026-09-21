<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('staff');
    $this->room = Room::factory()->create(['status' => 'active']);
});

test('user bisa berhasil membuat reservasi kalau tidak ada bentrok jadwal', function () {
    $startTime = now()->addDays(2)->setTime(10, 0, 0);
    $endTime = now()->addDays(2)->setTime(12, 0, 0);

    $response = $this->actingAs($this->user)->post(route('reservations.store'), [
        'room_id' => $this->room->id,
        'title' => 'Rapat Koordinasi Bebas Bentrok',
        'description' => 'Pembahasan agenda rutin',
        'start_time' => $startTime->toDateTimeString(),
        'end_time' => $endTime->toDateTimeString(),
    ]);

    $response->assertRedirect(route('reservations.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'title' => 'Rapat Koordinasi Bebas Bentrok',
        'status' => 'pending',
    ]);
});

test('user gagal membuat reservasi kalau waktu bentrok persis sama dengan reservasi lain yang statusnya approved', function () {
    $startTime = now()->addDays(2)->setTime(10, 0, 0);
    $endTime = now()->addDays(2)->setTime(12, 0, 0);

    Reservation::factory()->approved()->create([
        'room_id' => $this->room->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $response = $this->actingAs($this->user)->post(route('reservations.store'), [
        'room_id' => $this->room->id,
        'title' => 'Reservasi Bentrok Persis',
        'description' => 'Mencoba waktu yang sama',
        'start_time' => $startTime->toDateTimeString(),
        'end_time' => $endTime->toDateTimeString(),
    ]);

    $response->assertSessionHasErrors(['end_time']);
});

test('user gagal membuat reservasi kalau waktu overlap sebagian', function () {
    // Reservasi yang sudah ada: 10:00 - 12:00
    $startTime = now()->addDays(2)->setTime(10, 0, 0);
    $endTime = now()->addDays(2)->setTime(12, 0, 0);

    Reservation::factory()->approved()->create([
        'room_id' => $this->room->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    // Reservasi baru mulai di tengah-tengah: 11:00 - 13:00
    $newStart = now()->addDays(2)->setTime(11, 0, 0);
    $newEnd = now()->addDays(2)->setTime(13, 0, 0);

    $response = $this->actingAs($this->user)->post(route('reservations.store'), [
        'room_id' => $this->room->id,
        'title' => 'Reservasi Overlap Sebagian',
        'start_time' => $newStart->toDateTimeString(),
        'end_time' => $newEnd->toDateTimeString(),
    ]);

    $response->assertSessionHasErrors(['end_time']);
});

test('user BISA membuat reservasi di ruangan yang sama tapi di waktu yang berbeda', function () {
    // Reservasi yang sudah ada: 09:00 - 11:00
    $startTime = now()->addDays(2)->setTime(9, 0, 0);
    $endTime = now()->addDays(2)->setTime(11, 0, 0);

    Reservation::factory()->approved()->create([
        'room_id' => $this->room->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    // Reservasi baru di waktu berbeda (misal siang 13:00 - 15:00)
    $newStart = now()->addDays(2)->setTime(13, 0, 0);
    $newEnd = now()->addDays(2)->setTime(15, 0, 0);

    $response = $this->actingAs($this->user)->post(route('reservations.store'), [
        'room_id' => $this->room->id,
        'title' => 'Reservasi Waktu Berbeda',
        'start_time' => $newStart->toDateTimeString(),
        'end_time' => $newEnd->toDateTimeString(),
    ]);

    $response->assertRedirect(route('reservations.index'));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', [
        'room_id' => $this->room->id,
        'title' => 'Reservasi Waktu Berbeda',
    ]);
});

test('user BISA membuat reservasi bentrok waktu asalkan reservasi yang lama statusnya rejected atau cancelled', function () {
    $startTime = now()->addDays(3)->setTime(14, 0, 0);
    $endTime = now()->addDays(3)->setTime(16, 0, 0);

    // Reservasi lama tapi ditolak dan dibatalkan
    Reservation::factory()->rejected()->create([
        'room_id' => $this->room->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    Reservation::factory()->cancelled()->create([
        'room_id' => $this->room->id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    // User membuat reservasi baru di waktu persis sama
    $response = $this->actingAs($this->user)->post(route('reservations.store'), [
        'room_id' => $this->room->id,
        'title' => 'Reservasi Baru Menggantikan Yang Batal',
        'start_time' => $startTime->toDateTimeString(),
        'end_time' => $endTime->toDateTimeString(),
    ]);

    $response->assertRedirect(route('reservations.index'));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', [
        'room_id' => $this->room->id,
        'title' => 'Reservasi Baru Menggantikan Yang Batal',
        'status' => 'pending',
    ]);
});
