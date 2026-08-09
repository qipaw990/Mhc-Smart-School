<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Http\Request;

class GradebookApiController extends Controller
{
    /**
     * Get Gradebook / Assessment Scores
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        $student = $user->student;

        if ($student) {
            $assessments = Assessment::with(['subject', 'academicYear'])
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'student_name' => $student->name,
                    'class_name'   => $student->currentClass?->name ?? '-',
                    'assessments'  => $assessments,
                ],
            ]);
        }

        // For Teacher / Admin: filter by class_id or subject_id
        $query = Assessment::with(['student.currentClass', 'subject', 'teacher']);

        if ($request->filled('class_id')) {
            $classId = $request->class_id;
            $query->whereHas('student', fn($q) => $q->where('current_class_id', $classId));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $assessments = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $assessments,
        ]);
    }

    /**
     * Get Student Report Card Summary (Nilai Rapor)
     */
    public function raporSummary(Request $request)
    {
        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Rapor digital hanya dapat diakses oleh akun siswa.',
            ], 403);
        }

        $rapor = ReportCard::with(['details.subject', 'academicYear'])
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student' => [
                    'name'       => $student->name,
                    'nisn'       => $student->nisn,
                    'class_name' => $student->currentClass?->name ?? '-',
                    'major_name' => $student->currentClass?->major?->name ?? '-',
                ],
                'rapor'   => $rapor,
            ],
        ]);
    }
}
