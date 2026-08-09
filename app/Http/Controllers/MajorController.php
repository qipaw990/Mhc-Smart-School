<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\School;

use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::withCount(['classes', 'students'])->get();
        return view('master.majors', compact('majors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:majors,code',
            'name' => 'required|string|max:255',
            'head_of_major' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $school = School::first();

        Major::create(array_merge($validated, [
            'school_id' => $school->id,
            'status' => 'active',
        ]));

        return back()->with('success', 'Jurusan / Kompetensi Keahlian berhasil ditambahkan!');
    }

    public function update(Request $request, Major $major)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:majors,code,' . $major->id,
            'name' => 'required|string|max:255',
            'head_of_major' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $major->update($validated);

        return back()->with('success', 'Data Jurusan ' . $major->code . ' berhasil diperbarui!');
    }

    public function destroy(Major $major)
    {
        $major->delete();
        return back()->with('success', 'Jurusan berhasil dihapus (soft delete).');
    }
}
