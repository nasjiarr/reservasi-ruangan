<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Support\BreadcrumbMap;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BreadcrumbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_breadcrumb_map_generates_single_item_for_dashboard(): void
    {
        $breadcrumbs = BreadcrumbMap::generateBreadcrumbs('dashboard');

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Dashboard', $breadcrumbs[0]['label']);
        $this->assertNull($breadcrumbs[0]['href']);
    }

    public function test_breadcrumb_map_generates_hierarchy_for_known_routes(): void
    {
        $breadcrumbs = BreadcrumbMap::generateBreadcrumbs('rooms.create');

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals('Ruangan', $breadcrumbs[0]['label']);
        $this->assertNotNull($breadcrumbs[0]['href']);
        $this->assertEquals('Tambah Ruangan', $breadcrumbs[1]['label']);
        $this->assertNull($breadcrumbs[1]['href']);
    }

    public function test_breadcrumb_map_fallback_for_unknown_dot_route(): void
    {
        $breadcrumbs = BreadcrumbMap::generateBreadcrumbs('custom.create');

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals('Custom', $breadcrumbs[0]['label']);
        $this->assertEquals('Tambah', $breadcrumbs[1]['label']);
    }

    public function test_room_edit_passes_breadcrumb_override(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = Room::factory()->create(['name' => 'Ruang Akasia']);

        $this->actingAs($admin)
            ->get(route('rooms.edit', $room))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Rooms/Edit')
                ->where('breadcrumbOverride', 'Ruang Akasia')
                ->has('breadcrumbs', 2)
            );
    }

    public function test_room_show_passes_breadcrumb_override(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $room = Room::factory()->create(['name' => 'Ruang Bima']);

        $this->actingAs($user)
            ->get(route('rooms.show', $room))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Rooms/Show')
                ->where('breadcrumbOverride', 'Ruang Bima')
                ->has('breadcrumbs', 2)
            );
    }

    public function test_reservation_show_passes_breadcrumb_override(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'title' => 'Rapat Strategis Q4',
        ]);

        $this->actingAs($user)
            ->get(route('reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reservations/Show')
                ->where('breadcrumbOverride', 'Rapat Strategis Q4')
                ->has('breadcrumbs', 2)
            );
    }
}

