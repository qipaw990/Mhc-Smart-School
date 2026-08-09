<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ReportCard;
use App\Models\ReportCardExtracurricular;
use App\Models\ReportCardGrade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ReportCardGeneratorService;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::withCount('students')->get();

        $selectedClassId = $request->get('class_id', $classes->first()?->id);
        $selectedClass = SchoolClass::find($selectedClassId);

        $reportCards = ReportCard::with(['student', 'grades.subject'])
            ->where('class_id', $selectedClassId)
            ->where('academic_year_id', $ay?->id)
            ->orderBy('class_rank')
            ->get();

        return view('rapor.index', compact('classes', 'selectedClass', 'reportCards', 'ay'));
    }

    public function generateClass(Request $request, ReportCardGeneratorService $generator)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $ay = AcademicYear::where('is_active', true)->first();
        $result = $generator->generateForClass($validated['class_id'], $ay->id);

        if ($result['status'] === 'error') {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('rapor.index', ['class_id' => $validated['class_id']])
            ->with('success', 'E-Rapor berhasil di-generate secara otomatis untuk seluruh siswa di kelas!');
    }

    public function show(ReportCard $reportCard)
    {
        // Bersihkan nilai yatim atau mapel yang sudah dihapus / tidak sesuai jurusan
        $this->cleanupMismatchedGrades($reportCard);

        $reportCard->load(['student.currentClass', 'grades.subject', 'grades.teacher', 'extracurriculars']);
        $school = School::first();

        return view('rapor.show', compact('reportCard', 'school'));
    }

    public function updateNotes(Request $request, ReportCard $reportCard)
    {
        $validated = $request->validate([
            'sick_count' => 'required|integer|min:0',
            'permit_count' => 'required|integer|min:0',
            'absent_count' => 'required|integer|min:0',
            'homeroom_notes' => 'required|string',
            'promotion_status' => 'required|in:naik_kelas,tinggal_kelas,lulus,belum_lulus',
            'extracurriculars' => 'nullable|array',
        ]);

        $reportCard->update([
            'sick_count' => $validated['sick_count'],
            'permit_count' => $validated['permit_count'],
            'absent_count' => $validated['absent_count'],
            'homeroom_notes' => $validated['homeroom_notes'],
            'promotion_status' => $validated['promotion_status'],
        ]);

        if (isset($validated['extracurriculars'])) {
            $reportCard->extracurriculars()->delete();
            foreach ($validated['extracurriculars'] as $extra) {
                if (!empty($extra['name'])) {
                    ReportCardExtracurricular::create([
                        'report_card_id' => $reportCard->id,
                        'activity_name' => $extra['name'],
                        'predicate' => $extra['predicate'] ?? 'Baik',
                        'description' => $extra['description'] ?? 'Aktif mengikuti kegiatan ekstrakurikuler.',
                    ]);
                }
            }
        }

        return back()->with('success', 'Catatan Rapor dan data ekstrakurikuler berhasil diperbarui!');
    }

    public function printAkademik(ReportCard $reportCard)
    {
        // Bersihkan nilai yatim atau mapel yang sudah dihapus / tidak sesuai jurusan
        $this->cleanupMismatchedGrades($reportCard);

        $reportCard->load(['student.currentClass', 'grades.subject', 'grades.teacher', 'extracurriculars']);
        $school = School::first();

        return view('rapor.print_akademik', compact('reportCard', 'school'));
    }

    public function leger(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::all();

        $selectedClassId = $request->get('class_id', $classes->first()?->id);
        $selectedClass = SchoolClass::find($selectedClassId);

        // Filter mapel hanya yang relevan dengan jurusan rombel kelas ini
        $subjects = Subject::where('status', 'active')
            ->where(function ($q) use ($selectedClass) {
                $q->whereNull('major_id');
                if ($selectedClass && $selectedClass->major_id) {
                    $q->orWhere('major_id', $selectedClass->major_id);
                }
            })
            ->get();

        $reportCards = ReportCard::with(['student', 'grades.subject'])
            ->where('class_id', $selectedClassId)
            ->where('academic_year_id', $ay?->id)
            ->orderBy('class_rank')
            ->get();

        return view('rapor.leger', compact('classes', 'selectedClass', 'subjects', 'reportCards', 'ay'));
    }

    private function cleanupMismatchedGrades(ReportCard $reportCard): void
    {
        ReportCardGrade::whereDoesntHave('subject')->delete();

        $majorId = $reportCard->schoolClass?->major_id;
        if ($majorId) {
            $validSubjectIds = Subject::where(function ($q) use ($majorId) {
                $q->whereNull('major_id')->orWhere('major_id', $majorId);
            })->pluck('id');

            $reportCard->grades()->whereNotIn('subject_id', $validSubjectIds)->delete();
        }
    }
}
