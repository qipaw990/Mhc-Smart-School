<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentPortalApiController extends Controller
{
    private function getStudent(Request $request): ?Student
    {
        $user = $request->user();
        return Student::where('user_id', $user->id)->first();
    }

    /**
     * Get student scores (nilai) for active academic year
     */
    public function nilai(Request $request)
    {
        $student = $this->getStudent($request);
        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data siswa tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $ay = AcademicYear::where('is_active', true)->first();

        $assessments = Assessment::with(['subject', 'scores' => function ($q) use ($student) {
            $q->where('student_id', $student->id);
        }])
            ->where('academic_year_id', $ay?->id)
            ->where('class_id', $student->current_class_id)
            ->orderBy('date', 'desc')
            ->get();

        $bySubject = $assessments->groupBy(fn($a) => $a->subject?->name ?? 'Umum');

        $subjectAverages = [];
        foreach ($bySubject as $subjectName => $items) {
            $scores = $items->flatMap(fn($a) => $a->scores)->pluck('final_score')->filter()->values();
            $subjectAverages[$subjectName] = $scores->isNotEmpty()
                ? round($scores->average(), 1)
                : null;
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student'          => $student,
                'academic_year'    => $ay,
                'by_subject'       => $bySubject,
                'subject_averages' => $subjectAverages,
            ],
        ]);
    }

    /**
     * Get student attendance history & summary for active academic year
     */
    public function kehadiran(Request $request)
    {
        $student = $this->getStudent($request);
        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data siswa tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $ay = AcademicYear::where('is_active', true)->first();

        $records = Attendance::with('scheduleItem.subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay?->id)
            ->orderBy('date', 'desc')
            ->get();

        $summary = [
            'hadir' => $records->where('status', 'H')->count(),
            'sakit' => $records->where('status', 'S')->count(),
            'izin'  => $records->where('status', 'I')->count(),
            'alpha' => $records->where('status', 'A')->count(),
            'total' => $records->count(),
        ];
        $summary['persen_hadir'] = $summary['total'] > 0
            ? round(($summary['hadir'] / $summary['total']) * 100, 1)
            : 0;

        $byMonth = $records->groupBy(fn($r) => $r->date->format('Y-m'));

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student'       => $student,
                'academic_year' => $ay,
                'summary'       => $summary,
                'records'       => $records,
                'by_month'      => $byMonth,
            ],
        ]);
    }
}
