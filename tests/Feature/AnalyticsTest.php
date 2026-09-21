<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permission = Permission::firstOrCreate(['name' => 'view-reports']);

    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $adminRole->givePermissionTo($permission);

    $staffRole = Role::firstOrCreate(['name' => 'staff']);
});

test('tamu (guest) diarahkan ke login saat mengakses analytics', function () {
    $response = $this->get(route('analytics.index'));
    $response->assertRedirect(route('login'));
});

test('staff tanpa permission view-reports mendapat 403 forbidden di analytics', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $response = $this->actingAs($staff)->get(route('analytics.index'));
    $response->assertStatus(403);
});

test('admin dengan permission view-reports berhasil mengakses analytics dengan props lengkap', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $room = Room::factory()->create(['name' => 'Ruang VIP Testing']);
    Reservation::factory()->create([
        'room_id' => $room->id,
        'user_id' => $admin->id,
        'status' => 'approved',
        'start_time' => Carbon::now()->setTime(10, 0, 0),
        'end_time' => Carbon::now()->setTime(12, 0, 0),
        'created_at' => Carbon::now()->subDays(2),
    ]);

    $response = $this->actingAs($admin)->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Analytics/Index')
        ->has('filters')
        ->has('summary')
        ->has('lineChart')
        ->has('barChartTopRooms')
        ->has('doughnutStatus')
        ->has('horizontalBarHours')
        ->has('heatmap')
        ->where('summary.total_reservations.value', fn ($val) => $val >= 1)
    );
});

test('filter tanggal start_date dan end_date memfilter data analytics', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $startDate = Carbon::now()->subDays(10)->format('Y-m-d');
    $endDate = Carbon::now()->format('Y-m-d');

    $response = $this->actingAs($admin)->get(route('analytics.index', [
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Analytics/Index')
        ->where('filters.start_date', $startDate)
        ->where('filters.end_date', $endDate)
    );
});

