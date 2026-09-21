<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_dashboard_with_operational_command_center_props(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = Room::create([
            'name' => 'Ruang Eksekutif',
            'location' => 'Lantai 2',
            'capacity' => 15,
            'status' => 'active',
        ]);

        Reservation::create([
            'room_id' => $room->id,
            'user_id' => $admin->id,
            'title' => 'Rapat Direksi',
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addMinutes(60),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('greeting')
            ->has('todayDate')
            ->has('stats', fn (Assert $stats) => $stats
                ->where('can_approve', true)
                ->has('today_reservations')
                ->has('rooms_occupied_now')
                ->has('total_active_rooms')
                ->has('pending_approvals')
                ->has('checkins_today')
                ->etc()
            )
            ->has('roomStatuses')
            ->has('recentReservations')
            ->etc()
        );
    }

    public function test_staff_can_access_dashboard_with_staff_specific_stats(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('stats.can_approve', false)
            ->has('recentReservations')
            ->etc()
        );
    }
}

