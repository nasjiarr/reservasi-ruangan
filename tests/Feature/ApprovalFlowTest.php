<?php

use App\Models\CheckIn;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');

    $this->staff = User::factory()->create();
    $this->staff->assignRole('staff');

    $this->room = Room::factory()->create(['status' => 'active']);
    $this->reservation = Reservation::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->staff->id,
        'status' => 'pending',
    ]);
});

test('user dengan permission approve-reservation bisa approve reservasi pending, status berubah jadi approved, dan record baru muncul di tabel approvals', function () {
    $response = $this->actingAs($this->manager)
        ->post(route('approvals.approve', $this->reservation->id), [
            'note' => 'Disetujui untuk kegiatan koordinasi.',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'id' => $this->reservation->id,
        'status' => 'approved',
    ]);

    $this->assertDatabaseHas('approvals', [
        'reservation_id' => $this->reservation->id,
        'approver_id' => $this->manager->id,
        'status' => 'approved',
    ]);
});

test('user TANPA permission approve-reservation mendapat 403 forbidden saat mencoba approve', function () {
    $response = $this->actingAs($this->staff)
        ->post(route('approvals.approve', $this->reservation->id));

    $response->assertStatus(403);

    $this->assertDatabaseHas('reservations', [
        'id' => $this->reservation->id,
        'status' => 'pending',
    ]);
});

test('reject reservasi wajib mengisi note (assert validation error kalau note kosong)', function () {
    $response = $this->actingAs($this->manager)
        ->post(route('approvals.reject', $this->reservation->id), [
            'note' => '',
        ]);

    $response->assertSessionHasErrors(['note']);

    $this->assertDatabaseHas('reservations', [
        'id' => $this->reservation->id,
        'status' => 'pending',
    ]);
});

test('setelah approve, record CheckIn otomatis terbuat dengan qr_token terisi', function () {
    $this->actingAs($this->manager)
        ->post(route('approvals.approve', $this->reservation->id));

    $this->assertDatabaseHas('check_ins', [
        'reservation_id' => $this->reservation->id,
        'checked_in_at' => null,
    ]);

    $checkIn = CheckIn::where('reservation_id', $this->reservation->id)->first();
    expect($checkIn)->not->toBeNull();
    expect($checkIn->qr_token)->not->toBeEmpty();
});

