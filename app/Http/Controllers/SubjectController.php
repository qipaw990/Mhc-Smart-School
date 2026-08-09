<?php

namespace App\Http\Controllers;

use App\Models\Major;
use App\Models\ReportCardGrade;
use App\Models\School;
use App\Models\Subject;
use App\Services\ExcelService;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::with(['major', 'learningOutcomes']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('phase')) {
            $query->where('phase', $request->phase);
        }

        $subjects = $query->paginate(15);
        $majors = Major::all();

        return view('curriculum.subjects', compact('subjects', 'majors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:subjects,code',
            'name' => 'required|string|max:255',
            'group' => 'required|in:A_general,B_vocational,C_concentration,mulok,p5',
            'phase' => 'required|in:E,F',
            'type' => 'required|in:theory,practice,theory_practice',
            'hours_per_week' => 'required|integer|min:1',
            'total_hours' => 'required|integer|min:1',
            'major_id' => 'nullable|exists:majors,id',
        ]);

        $school = School::first();

        Subject::create(array_merge($validated, [
            'school_id' => $school->id,
            'status' => 'active',
        ]));

        return back()->with('success', 'Mata Pelajaran ' . $validated['name'] . ' berhasil ditambahkan!');
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:subjects,code,' . $subject->id,
            'name' => 'required|string|max:255',
            'group' => 'required|in:A_general,B_vocational,C_concentration,mulok,p5',
            'phase' => 'required|in:E,F',
            'type' => 'required|in:theory,practice,theory_practice',
            'hours_per_week' => 'required|integer|min:1',
            'total_hours' => 'required|integer|min:1',
            'major_id' => 'nullable|exists:majors,id',
            'status' => 'required|in:active,inactive',
        ]);

        $subject->update($validated);

        return back()->with('success', 'Mata Pelajaran ' . $subject->name . ' berhasil diperbarui!');
    }

    public function destroy(Subject $subject)
    {
        ReportCardGrade::where('subject_id', $subject->id)->delete();
        $subject->delete();
        return back()->with('success', 'Mata Pelajaran berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return ExcelService::downloadStyledExcel(
            'template_import_mapel.xls',
            'TEMPLATE IMPORT DATA MATA PELAJARAN',
            ['kode', 'nama', 'kelompok', 'fase', 'jam_per_minggu'],
            [
                ['MTK-01', 'Matematika Dasar', 'A_general', 'E', '4'],
                ['TKM-01', 'Pemeliharaan Mesin Sepeda Motor', 'B_vocational', 'F', '6'],
            ],
            [
                'Isi data sesuai kolom yang tersedia.',
                'Kelompok diisi salah satu: A_general, B_vocational, C_concentration, mulok, atau p5.',
                'Fase diisi: E (Kelas X) atau F (Kelas XI & XII).'
            ]
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $school = School::first();
        $importedCount = 0;
        $skippedCount = 0;

        $rows = ExcelService::parseUploadedFile($request->file('file'));

        foreach ($rows as $data) {
            $code = trim($data[0] ?? '');
            $name = trim($data[1] ?? '');
            if (empty($code) || empty($name) || strtolower($code) === 'kode' || str_contains(strtolower($code), 'template')) continue;

            $group = trim($data[2] ?? 'A_general') ?: 'A_general';
            if (!in_array($group, ['A_general', 'B_vocational', 'C_concentration', 'mulok', 'p5'])) {
                $group = 'A_general';
            }

            $phase = strtoupper(trim($data[3] ?? 'E'));
            if (!in_array($phase, ['E', 'F'])) $phase = 'E';

            $hours = (int) (trim($data[4] ?? 4) ?: 4);

            if (Subject::where('code', $code)->exists()) {
                $skippedCount++;
                continue;
            }

            Subject::create([
                'school_id' => $school->id,
                'code' => $code,
                'name' => $name,
                'group' => $group,
                'phase' => $phase,
                'type' => 'theory_practice',
                'hours_per_week' => $hours,
                'total_hours' => $hours * 18,
                'status' => 'active',
            ]);

            $importedCount++;
        }

        return back()->with('success', "Import Excel Mata Pelajaran berhasil! {$importedCount} data ditambahkan, {$skippedCount} data dilewati (duplikat).");
    }
}
