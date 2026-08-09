<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\LearningObjective;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $ay = AcademicYear::where('is_active', true)->first();
        $query = Assessment::with(['teacher', 'subject', 'schoolClass', 'learningObjective'])
            ->where('academic_year_id', $ay?->id)
            ->orderBy('date', 'desc');

        if ($isGuru && $teacher) {
            $query->where('teacher_id', $teacher->id);
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

        $assessments = $query->paginate(15);
        $classes = SchoolClass::all();
        $subjects = Subject::where('status', 'active')->get();

        return view('gradebook.index', compact('assessments', 'classes', 'subjects', 'ay', 'isGuru', 'teacher'));
    }

    public function create()
    {
        $user    = auth()->user();
        $isGuru  = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $activeScheduleId = \App\Models\Schedule::where('status', 'active')->value('id');

        if ($isGuru && $teacher) {
            // Scope subjects and classes to teacher's own active schedule slots
            $mySlots = \App\Models\ScheduleItem::where('teacher_id', $teacher->id)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with(['subject', 'schoolClass'])
                ->get();

            $subjects  = $mySlots->pluck('subject')->filter()->unique('id')->sortBy('name')->values();
            $classes   = $mySlots->pluck('schoolClass')->filter()->unique('id')->sortBy('name')->values();
            $teachers  = collect([$teacher]);
            $learningObjectives = LearningObjective::where('status', 'active')
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->get();
        } else {
            $teachers  = Teacher::all();
            $classes   = SchoolClass::all();
            $subjects  = Subject::where('status', 'active')->get();
            $learningObjectives = LearningObjective::where('status', 'active')->get();
        }

        return view('gradebook.create', compact('teachers', 'classes', 'subjects', 'learningObjectives', 'isGuru', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'learning_objective_id' => 'nullable|exists:learning_objectives,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:diagnostic,formative,summative_tp,summative_semester',
            'kktp_score' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:1|max:100',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();

        $assessment = Assessment::create(array_merge($validated, [
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $semester?->id,
        ]));

        // Pre-populate student list with default score 0
        $students = Student::where('current_class_id', $validated['class_id'])->get();
        foreach ($students as $s) {
            AssessmentScore::create([
                'assessment_id' => $assessment->id,
                'student_id' => $s->id,
                'score' => 0.00,
                'final_score' => 0.00,
                'achievement_status' => 'not_achieved',
            ]);
        }

        return redirect()->route('gradebook.scores', $assessment->id)
            ->with('success', 'Asesmen berhasil dibuat! Silakan input nilai peserta didik.');
    }

    public function scores(Assessment $assessment)
    {
        $assessment->load(['subject', 'teacher', 'schoolClass', 'learningObjective']);
        $scores = AssessmentScore::with('student')
            ->where('assessment_id', $assessment->id)
            ->get();

        // Calculate statistics
        $avgScore = $scores->avg('final_score') ?? 0;
        $achievedCount = $scores->where('achievement_status', 'achieved')->count();
        $notAchievedCount = $scores->where('achievement_status', 'not_achieved')->count();
        $remedialCount = $scores->where('is_remedial', true)->count();

        return view('gradebook.scores', compact(
            'assessment',
            'scores',
            'avgScore',
            'achievedCount',
            'notAchievedCount',
            'remedialCount'
        ));
    }

    public function storeScores(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*.score' => 'required|numeric|min:0|max:100',
            'scores.*.is_remedial' => 'nullable|boolean',
            'scores.*.remedial_score' => 'nullable|numeric|min:0|max:100',
            'scores.*.teacher_notes' => 'nullable|string',
        ]);

        foreach ($validated['scores'] as $studentId => $data) {
            $rawScore = $data['score'];
            $isRemedial = !empty($data['is_remedial']);
            $remedialScore = $isRemedial ? ($data['remedial_score'] ?? null) : null;
            $finalScore = ($isRemedial && $remedialScore !== null) ? $remedialScore : $rawScore;
            $status = $finalScore >= $assessment->kktp_score ? 'achieved' : 'not_achieved';

            AssessmentScore::updateOrCreate([
                'assessment_id' => $assessment->id,
                'student_id' => $studentId,
            ], [
                'score' => $rawScore,
                'is_remedial' => $isRemedial,
                'remedial_score' => $remedialScore,
                'final_score' => $finalScore,
                'achievement_status' => $status,
                'teacher_notes' => $data['teacher_notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Nilai Asesmen berhasil disimpan dan KKTP telah dikalkulasi!');
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();
        return redirect()->route('gradebook.index')->with('success', 'Asesmen berhasil dihapus.');
    }
}
