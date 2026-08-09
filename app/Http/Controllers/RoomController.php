<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\School;

use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('master.rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:rooms,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:classroom,lab,workshop,hall,library',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
        ]);

        $school = School::first();

        Room::create(array_merge($validated, [
            'school_id' => $school->id,
            'status' => 'active',
        ]));

        return back()->with('success', 'Ruangan / Lab berhasil ditambahkan!');
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:rooms,code,' . $room->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:classroom,lab,workshop,hall,library',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,maintenance',
        ]);

        $room->update($validated);

        return back()->with('success', 'Data Ruangan ' . $room->code . ' berhasil diperbarui!');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Ruangan berhasil dihapus (soft delete).');
    }
}
