<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;

use App\Models\Major;
use App\Models\Room;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\ExcelService;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::with(['major', 'room', 'homeroomTeacher', 'students'])
            ->where('academic_year_id', $ay?->id)
            ->get();

        $majors = Major::all();
        $rooms = Room::all();
        $teachers = Teacher::all();

        return view('master.classes', compact('classes', 'majors', 'rooms', 'teachers', 'ay'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'grade_level' => 'required|in:X,XI,XII',
            'major_id' => 'required|exists:majors,id',
            'room_id' => 'nullable|exists:rooms,id',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
            'capacity' => 'required|integer|min:1',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();

        SchoolClass::create(array_merge($validated, [
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
        ]));

        return back()->with('success', 'Kelas ' . $validated['name'] . ' berhasil ditambahkan!');
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'grade_level' => 'required|in:X,XI,XII',
            'major_id' => 'required|exists:majors,id',
            'room_id' => 'nullable|exists:rooms,id',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
            'capacity' => 'required|integer|min:1',
        ]);

        $class->update($validated);

        return back()->with('success', 'Data Kelas ' . $class->name . ' berhasil diperbarui!');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return ExcelService::downloadStyledExcel(
            'template_import_kelas.xls',
            'TEMPLATE IMPORT DATA ROMBEL & KELAS',
            ['nama_kelas', 'tingkat', 'jurusan_kode', 'kapasitas'],
            [
                ['X TKM 1', 'X', 'TKM', '36'],
                ['XI TBSM 1', 'XI', 'TBSM', '36'],
            ],
            [
                'Isi data sesuai kolom yang tersedia.',
                'Tingkat diisi: X, XI, atau XII.',
                'Kode Jurusan disesuaikan dengan daftar Jurusan di sistem (contoh: TKM, TBSM, RPL).',
                'Kelas akan otomatis masuk ke Tahun Ajaran yang sedang aktif.'
            ]
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $majorsMap = Major::pluck('id', 'code')->toArray();
        $defaultMajorId = !empty($majorsMap) ? reset($majorsMap) : null;

        $importedCount = 0;
        $skippedCount = 0;

        $rows = ExcelService::parseUploadedFile($request->file('file'));

        foreach ($rows as $data) {
            $name = trim($data[0] ?? '');
            if (empty($name) || strtolower($name) === 'nama_kelas' || str_contains(strtolower($name), 'template')) continue;

            $gradeLevel = strtoupper(trim($data[1] ?? 'X'));
            if (!in_array($gradeLevel, ['X', 'XI', 'XII'])) $gradeLevel = 'X';

            $majorCode = trim($data[2] ?? '');
            $capacity = (int) (trim($data[3] ?? 36) ?: 36);
            $majorId = $majorsMap[$majorCode] ?? $defaultMajorId;

            if (SchoolClass::where('academic_year_id', $ay->id)->where('name', $name)->exists()) {
                $skippedCount++;
                continue;
            }

            SchoolClass::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'name' => $name,
                'grade_level' => $gradeLevel,
                'major_id' => $majorId,
                'capacity' => $capacity,
            ]);

            $importedCount++;
        }

        return back()->with('success', "Import Excel Kelas berhasil! {$importedCount} data ditambahkan, {$skippedCount} data dilewati (duplikat).");
    }
}
