<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;

use App\Models\School;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::with('semesters')->orderBy('start_date', 'desc')->get();
        return view('master.academic_year', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $school = School::first();

        DB::transaction(function () use ($school, $validated) {
            $ay = AcademicYear::create([
                'school_id' => $school->id,
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => false,
            ]);

            Semester::create([
                'academic_year_id' => $ay->id,
                'name' => 'Ganjil',
                'semester_number' => 1,
                'is_active' => true,
            ]);

            Semester::create([
                'academic_year_id' => $ay->id,
                'name' => 'Genap',
                'semester_number' => 2,
                'is_active' => false,
            ]);
        });

        return back()->with('success', 'Tahun Ajaran baru berhasil dibuat dengan Semester Ganjil & Genap!');
    }

    public function setActive(AcademicYear $academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
        });

        return back()->with('success', 'Tahun Ajaran ' . $academicYear->name . ' berhasil diaktifkan!');
    }

    public function setActiveSemester(Semester $semester)
    {
        DB::transaction(function () use ($semester) {
            Semester::where('academic_year_id', $semester->academic_year_id)->update(['is_active' => false]);
            $semester->update(['is_active' => true]);
        });

        return back()->with('success', 'Semester ' . $semester->name . ' berhasil diaktifkan!');
    }
}
