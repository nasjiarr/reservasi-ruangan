<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->except(['index', 'show']);
    }

    /**
     * Display a listing of the rooms.
     */
    public function index(): Response
    {
        $rooms = Room::with('facilities')
            ->latest()
            ->paginate(10);

        return Inertia::render('Rooms/Index', [
            'rooms' => $rooms,
        ]);
    }

    /**
     * Show the form for creating a new room.
     */
    public function create(): Response
    {
        $facilities = Facility::all(['id', 'name']);

        return Inertia::render('Rooms/Create', [
            'facilities' => $facilities,
        ]);
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $facilityIds = $validated['facilities'] ?? [];
        unset($validated['facilities']);

        $room = Room::create($validated);

        if (!empty($facilityIds)) {
            $room->facilities()->sync($facilityIds);
        }

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Display the specified room.
     */
    public function show(Room $room): Response
    {
        $room->load('facilities');

        return Inertia::render('Rooms/Show', [
            'room' => $room,
        ]);
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit(Room $room): Response
    {
        $room->load('facilities');
        $facilities = Facility::all(['id', 'name']);

        return Inertia::render('Rooms/Edit', [
            'room' => $room,
            'facilities' => $facilities,
        ]);
    }

    /**
     * Update the specified room in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $validated = $request->validated();
        $facilityIds = $validated['facilities'] ?? [];
        unset($validated['facilities']);

        $room->update($validated);
        $room->facilities()->sync($facilityIds);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil diperbarui.');
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}

