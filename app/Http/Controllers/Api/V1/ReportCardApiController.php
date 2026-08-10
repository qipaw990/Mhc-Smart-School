<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ReportCard;
use App\Models\ReportCardExtracurricular;
use App\Models\ReportCardGrade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ReportCardGeneratorService;
use Illuminate\Http\Request;

class ReportCardApiController extends Controller
{
    /**
     * List report cards by class
     */
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classId = $request->input('class_id');

        $query = ReportCard::with(['student.currentClass', 'grades.subject'])
            ->where('academic_year_id', $ay?->id);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $reportCards = $query->orderBy('class_rank')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => [
                'academic_year' => $ay,
                'report_cards'  => $reportCards,
            ],
        ]);
    }

    /**
     * Generate class report card
     */
    public function generateClass(Request $request, ReportCardGeneratorService $generator)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $ay = AcademicYear::where('is_active', true)->first();
        if (!$ay) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak ada Tahun Akademik aktif.',
            ], 422);
        }

        $result = $generator->generateForClass($validated['class_id'], $ay->id);

        if ($result['status'] === 'error') {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'E-Rapor berhasil di-generate secara otomatis untuk seluruh siswa di kelas!',
        ]);
    }

    /**
     * Show report card details
     */
    public function show(ReportCard $reportCard)
    {
        $this->cleanupMismatchedGrades($reportCard);

        $reportCard->load(['student.currentClass', 'grades.subject', 'grades.teacher', 'extracurriculars']);
        $school = School::first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'school'      => $school,
                'report_card' => $reportCard,
            ],
        ]);
    }

    /**
     * Update notes & extracurriculars
     */
    public function updateNotes(Request $request, ReportCard $reportCard)
    {
        $validated = $request->validate([
            'sick_count'       => 'required|integer|min:0',
            'permit_count'     => 'required|integer|min:0',
            'absent_count'     => 'required|integer|min:0',
            'homeroom_notes'   => 'required|string',
            'promotion_status' => 'required|in:naik_kelas,tinggal_kelas,lulus,belum_lulus',
            'extracurriculars' => 'nullable|array',
        ]);

        $reportCard->update([
            'sick_count'       => $validated['sick_count'],
            'permit_count'     => $validated['permit_count'],
            'absent_count'     => $validated['absent_count'],
            'homeroom_notes'   => $validated['homeroom_notes'],
            'promotion_status' => $validated['promotion_status'],
        ]);

        if (isset($validated['extracurriculars'])) {
            $reportCard->extracurriculars()->delete();
            foreach ($validated['extracurriculars'] as $extra) {
                if (!empty($extra['name'])) {
                    ReportCardExtracurricular::create([
                        'report_card_id' => $reportCard->id,
                        'activity_name'  => $extra['name'],
                        'predicate'      => $extra['predicate'] ?? 'Baik',
                        'description'    => $extra['description'] ?? 'Aktif mengikuti kegiatan ekstrakurikuler.',
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Catatan Rapor dan data ekstrakurikuler berhasil diperbarui!',
            'data'    => $reportCard->load('extracurriculars'),
        ]);
    }

    /**
     * Get Leger data
     */
    public function leger(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classId = $request->input('class_id');

        $selectedClass = SchoolClass::find($classId);

        $subjects = Subject::where('status', 'active')
            ->where(function ($q) use ($selectedClass) {
                $q->whereNull('major_id');
                if ($selectedClass && $selectedClass->major_id) {
                    $q->orWhere('major_id', $selectedClass->major_id);
                }
            })
            ->get();

        $reportCards = ReportCard::with(['student', 'grades.subject'])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->where('academic_year_id', $ay?->id)
            ->orderBy('class_rank')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'academic_year' => $ay,
                'class'         => $selectedClass,
                'subjects'      => $subjects,
                'report_cards'  => $reportCards,
            ],
        ]);
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
