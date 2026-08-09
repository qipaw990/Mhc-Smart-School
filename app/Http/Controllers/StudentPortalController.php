<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    /**
     * Get the logged-in student record.
     */
    private function getStudent(): ?Student
    {
        $user = Auth::user();
        return Student::where('user_id', $user->id)->first();
    }

    /**
     * Student portal: nilai (assessment scores) for active academic year.
     */
    public function nilai()
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan untuk akun ini.');
        }

        $ay = AcademicYear::where('is_active', true)->first();

        // Get all assessments for student's class in active academic year,
        // with scores for this student
        $assessments = Assessment::with(['subject', 'scores' => function ($q) use ($student) {
            $q->where('student_id', $student->id);
        }])
            ->where('academic_year_id', $ay?->id)
            ->where('class_id', $student->current_class_id)
            ->orderBy('date', 'desc')
            ->get();

        // Group by subject for better display
        $bySubject = $assessments->groupBy(fn($a) => $a->subject?->name ?? 'Umum');

        // Compute per-subject average from final_score
        $subjectAverages = [];
        foreach ($bySubject as $subjectName => $items) {
            $scores = $items->flatMap(fn($a) => $a->scores)->pluck('final_score')->filter()->values();
            $subjectAverages[$subjectName] = $scores->isNotEmpty()
                ? round($scores->average(), 1)
                : null;
        }

        return view('student.nilai', compact('student', 'ay', 'bySubject', 'subjectAverages'));
    }

    /**
     * Student portal: kehadiran (attendance) for active academic year.
     */
    public function kehadiran()
    {
        $student = $this->getStudent();
        if (!$student) {
            return back()->with('error', 'Data siswa tidak ditemukan untuk akun ini.');
        }

        $ay = AcademicYear::where('is_active', true)->first();

        // All attendance records for this student in active year
        $records = Attendance::with('scheduleItem.subject')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay?->id)
            ->orderBy('date', 'desc')
            ->get();

        // Summary counts
        $summary = [
            'hadir'   => $records->where('status', 'hadir')->count(),
            'sakit'   => $records->where('status', 'sakit')->count(),
            'izin'    => $records->where('status', 'izin')->count(),
            'alpha'   => $records->where('status', 'alpha')->count(),
            'total'   => $records->count(),
        ];
        $summary['persen_hadir'] = $summary['total'] > 0
            ? round(($summary['hadir'] / $summary['total']) * 100, 1)
            : 0;

        // Group by month for chart/section display
        $byMonth = $records->groupBy(fn($r) => $r->date->format('Y-m'));

        return view('student.kehadiran', compact('student', 'ay', 'records', 'summary', 'byMonth'));
    }
}
