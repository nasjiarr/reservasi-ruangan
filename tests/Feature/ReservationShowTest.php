<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReservationShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_owner_can_view_their_reservation_detail(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $room = Room::factory()->create(['name' => 'Ruang Cempaka']);
        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'title' => 'Rapat Evaluasi Sprint',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reservations/Show')
                ->has('reservation')
                ->where('reservation.id', $reservation->id)
                ->where('reservation.title', 'Rapat Evaluasi Sprint')
                ->where('reservation.status', 'approved')
            );
    }

    public function test_admin_can_view_any_reservation_detail(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $staff->id,
            'room_id' => $room->id,
            'title' => 'Diskusi Tim Desain',
        ]);

        $this->actingAs($admin)
            ->get(route('reservations.show', $reservation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reservations/Show')
                ->where('reservation.title', 'Diskusi Tim Desain')
            );
    }

    public function test_other_staff_cannot_view_unauthorized_reservation(): void
    {
        $staff1 = User::factory()->create();
        $staff1->assignRole('staff');

        $staff2 = User::factory()->create();
        $staff2->assignRole('staff');

        $room = Room::factory()->create();
        $reservation = Reservation::factory()->create([
            'user_id' => $staff1->id,
            'room_id' => $room->id,
        ]);

        $this->actingAs($staff2)
            ->get(route('reservations.show', $reservation))
            ->assertForbidden();
    }
}

