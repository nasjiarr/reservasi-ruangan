<?php

use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->staff = User::factory()->create();
    $this->staff->assignRole('staff');
});

test('admin bisa membuat ruangan baru', function () {
    $payload = [
        'name' => 'Ruang Auditorium Utama',
        'location' => 'Lantai 3 Gedung A',
        'capacity' => 100,
        'description' => 'Ruangan besar untuk seminar dan rapat umum',
        'status' => 'active',
    ];

    $response = $this->actingAs($this->admin)
        ->post(route('rooms.store'), $payload);

    $response->assertRedirect(route('rooms.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('rooms', [
        'name' => 'Ruang Auditorium Utama',
        'capacity' => 100,
        'status' => 'active',
    ]);
});

test('user role staff TIDAK bisa membuat ruangan (403 forbidden)', function () {
    $payload = [
        'name' => 'Ruang Rahasia Staff',
        'location' => 'Lantai 1',
        'capacity' => 10,
        'status' => 'active',
    ];

    $response = $this->actingAs($this->staff)
        ->post(route('rooms.store'), $payload);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('rooms', [
        'name' => 'Ruang Rahasia Staff',
    ]);
});

test('staff tetap bisa melihat daftar ruangan (index)', function () {
    Room::factory()->count(3)->create();

    $response = $this->actingAs($this->staff)
        ->get(route('rooms.index'));

    $response->assertStatus(200);
});

