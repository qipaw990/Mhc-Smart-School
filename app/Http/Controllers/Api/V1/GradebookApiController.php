<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\ReportCard;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;

class GradebookApiController extends Controller
{
    /**
     * Get Gradebook / Assessment List
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

        $query = Assessment::with(['student.currentClass', 'subject', 'teacher', 'schoolClass']);

        if ($user->teacher) {
            $query->where('teacher_id', $user->teacher->id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $assessments = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $assessments,
        ]);
    }

    /**
     * Store New Assessment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'            => 'required|exists:teachers,id',
            'class_id'              => 'required|exists:classes,id',
            'subject_id'            => 'required|exists:subjects,id',
            'learning_objective_id' => 'nullable|exists:learning_objectives,id',
            'title'                 => 'required|string|max:255',
            'type'                  => 'required|in:diagnostic,formative,summative_tp,summative_semester',
            'kktp_score'            => 'required|numeric|min:0|max:100',
            'max_score'             => 'required|numeric|min:1|max:100',
            'date'                  => 'required|date',
            'description'           => 'nullable|string',
        ]);

        $school   = School::first();
        $ay       = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay?->id)->where('is_active', true)->first();

        $assessment = Assessment::create(array_merge($validated, [
            'school_id'        => $school?->id,
            'academic_year_id' => $ay?->id,
            'semester_id'      => $semester?->id,
        ]));

        $students = Student::where('current_class_id', $validated['class_id'])->get();
        foreach ($students as $s) {
            AssessmentScore::create([
                'assessment_id'      => $assessment->id,
                'student_id'         => $s->id,
                'score'              => 0.00,
                'final_score'        => 0.00,
                'achievement_status' => 'not_achieved',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Asesmen berhasil dibuat! Silakan input nilai peserta didik.',
            'data'    => $assessment,
        ], 201);
    }

    /**
     * Get Scores for an Assessment
     */
    public function scores(Assessment $assessment)
    {
        $assessment->load(['subject', 'teacher', 'schoolClass', 'learningObjective']);
        $scores = AssessmentScore::with('student')
            ->where('assessment_id', $assessment->id)
            ->get();

        $avgScore         = $scores->avg('final_score') ?? 0;
        $achievedCount    = $scores->where('achievement_status', 'achieved')->count();
        $notAchievedCount = $scores->where('achievement_status', 'not_achieved')->count();
        $remedialCount    = $scores->where('is_remedial', true)->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'assessment'         => $assessment,
                'scores'             => $scores,
                'avg_score'          => round($avgScore, 2),
                'achieved_count'     => $achievedCount,
                'not_achieved_count' => $notAchievedCount,
                'remedial_count'     => $remedialCount,
            ],
        ]);
    }

    /**
     * Store Scores for an Assessment
     */
    public function storeScores(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'scores'                         => 'required|array',
            'scores.*.score'                 => 'required|numeric|min:0|max:100',
            'scores.*.is_remedial'           => 'nullable|boolean',
            'scores.*.remedial_score'        => 'nullable|numeric|min:0|max:100',
            'scores.*.teacher_notes'         => 'nullable|string',
        ]);

        foreach ($validated['scores'] as $studentId => $data) {
            $rawScore      = $data['score'];
            $isRemedial    = !empty($data['is_remedial']);
            $remedialScore = $isRemedial ? ($data['remedial_score'] ?? null) : null;
            $finalScore    = ($isRemedial && $remedialScore !== null) ? $remedialScore : $rawScore;
            $status        = $finalScore >= $assessment->kktp_score ? 'achieved' : 'not_achieved';

            AssessmentScore::updateOrCreate([
                'assessment_id' => $assessment->id,
                'student_id'    => $studentId,
            ], [
                'score'              => $rawScore,
                'is_remedial'        => $isRemedial,
                'remedial_score'     => $remedialScore,
                'final_score'        => $finalScore,
                'achievement_status' => $status,
                'teacher_notes'      => $data['teacher_notes'] ?? null,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Nilai Asesmen berhasil disimpan!',
        ]);
    }

    /**
     * Delete Assessment
     */
    public function destroy(Assessment $assessment)
    {
        $assessment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Asesmen berhasil dihapus.',
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
