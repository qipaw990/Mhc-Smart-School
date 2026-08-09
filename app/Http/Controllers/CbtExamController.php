<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamClass;
use App\Models\QuestionBank;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentExam;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CbtExamController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $ay = AcademicYear::where('is_active', true)->first();
        $query = Exam::with(['questionBank', 'teacher', 'subject', 'examClasses.schoolClass'])
            ->withCount('studentExams')
            ->where('academic_year_id', $ay?->id)
            ->orderBy('start_time', 'desc');

        if ($isGuru && $teacher) {
            $query->where('teacher_id', $teacher->id);
        }

        $exams = $query->paginate(10);

        return view('cbt.exams.index', compact('exams', 'ay', 'isGuru', 'teacher'));
    }

    public function create()
    {
        $user = auth()->user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $activeScheduleId = \App\Models\Schedule::where('status', 'active')->value('id');

        if ($isGuru && $teacher) {
            $mySlots = \App\Models\ScheduleItem::where('teacher_id', $teacher->id)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with(['subject', 'schoolClass'])
                ->get();

            $subjects = $mySlots->pluck('subject')->filter()->unique('id')->sortBy('name')->values();
            if ($subjects->isEmpty()) {
                $loadSubjects = $teacher->teachingLoads()->with('subject')->get()->pluck('subject')->filter();
                $subjects = $loadSubjects->unique('id')->sortBy('name')->values();
            }

            $classes = $mySlots->pluck('schoolClass')->filter()->unique('id')->sortBy('name')->values();
            if ($classes->isEmpty()) {
                $loadClasses = $teacher->teachingLoads()->with('schoolClass')->get()->pluck('schoolClass')->filter();
                $classes = $loadClasses->unique('id')->sortBy('name')->values();
            }

            $banks = QuestionBank::where('teacher_id', $teacher->id)->with('subject')->get();
            $teachers = collect([$teacher]);
        } else {
            $banks = QuestionBank::with('subject')->get();
            $teachers = Teacher::all();
            $subjects = Subject::where('status', 'active')->get();
            $classes = SchoolClass::all();
        }

        return view('cbt.exams.create', compact('banks', 'teachers', 'subjects', 'classes', 'isGuru', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_bank_id' => 'required|exists:question_banks,id',
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'token' => 'required|string|max:10',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:10|max:240',
            'kktp_score' => 'required|numeric|min:0|max:100',
            'max_tab_switches' => 'required|integer|min:1|max:10',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
            'instructions' => 'nullable|string',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();

        $exam = Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $semester?->id,
            'question_bank_id' => $validated['question_bank_id'],
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'token' => strtoupper($validated['token']),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'kktp_score' => $validated['kktp_score'],
            'max_tab_switches' => $validated['max_tab_switches'],
            'randomize_questions' => $request->has('randomize_questions'),
            'randomize_options' => $request->has('randomize_options'),
            'status' => 'published',
            'instructions' => $validated['instructions'],
        ]);

        foreach ($validated['class_ids'] as $classId) {
            ExamClass::create([
                'exam_id' => $exam->id,
                'class_id' => $classId,
            ]);
        }

        return redirect()->route('cbt.exams.index')->with('success', 'Jadwal Ujian CBT berhasil dipublikasikan!');
    }

    public function monitor(Exam $exam)
    {
        $exam->load(['questionBank', 'teacher', 'subject', 'examClasses.schoolClass']);
        $studentExams = StudentExam::with('student.currentClass')
            ->where('exam_id', $exam->id)
            ->orderBy('start_time', 'desc')
            ->get();

        // Statistics
        $totalParticipants = $studentExams->count();
        $inProgressCount = $studentExams->where('status', 'in_progress')->count();
        $submittedCount = $studentExams->where('status', 'submitted')->count();
        $blockedCount = $studentExams->where('status', 'blocked')->count();

        return view('cbt.exams.monitor', compact(
            'exam',
            'studentExams',
            'totalParticipants',
            'inProgressCount',
            'submittedCount',
            'blockedCount'
        ));
    }

    public function refreshToken(Exam $exam)
    {
        $newToken = strtoupper(Str::random(6));
        $exam->update(['token' => $newToken]);

        return back()->with('success', 'Token Ujian berhasil di-refresh menjadi: ' . $newToken);
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('cbt.exams.index')->with('success', 'Jadwal ujian berhasil dihapus.');
    }
}
