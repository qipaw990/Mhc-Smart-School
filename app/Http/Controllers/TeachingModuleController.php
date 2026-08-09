<?php

namespace App\Http\Controllers;

use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeachingModuleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $query = TeachingModule::with(['subject', 'teacher', 'schoolClass', 'learningOutcome', 'learningObjective'])
            ->orderBy('created_at', 'desc');

        if ($isGuru && $teacher) {
            $query->where('teacher_id', $teacher->id);
        }

        $modules = $query->paginate(10);

        return view('curriculum.modules.index', compact('modules', 'isGuru', 'teacher'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
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

            $teachers = collect([$teacher]);
            $learningOutcomes = LearningOutcome::where('status', 'active')
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->get();
            $learningObjectives = LearningObjective::where('status', 'active')
                ->whereIn('learning_outcome_id', $learningOutcomes->pluck('id'))
                ->get();
        } else {
            $subjects = Subject::where('status', 'active')->get();
            $teachers = Teacher::all();
            $classes = SchoolClass::all();
            $learningOutcomes = LearningOutcome::where('status', 'active')->get();
            $learningObjectives = LearningObjective::where('status', 'active')->get();
        }

        return view('curriculum.modules.create', compact('subjects', 'teachers', 'classes', 'learningOutcomes', 'learningObjectives', 'isGuru', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'nullable|exists:classes,id',
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'title' => 'required|string|max:255',
            'phase' => 'required|in:E,F',
            'grade_level' => 'required|in:X,XI,XII',
            'allocated_hours' => 'required|integer|min:1',
            'learning_model' => 'required|string|max:100',
            'methods' => 'required|string|max:255',
            'target_students' => 'required|string|max:100',
            'preliminary_activities' => 'required|string',
            'core_activities' => 'required|string',
            'closing_activities' => 'required|string',
            'diagnostic_assessment' => 'nullable|string',
            'formative_assessment' => 'nullable|string',
            'summative_assessment' => 'nullable|string',
            'remedial_plan' => 'nullable|string',
            'enrichment_plan' => 'nullable|string',
            'student_worksheet' => 'nullable|string',
            'assessment_rubric' => 'nullable|string',
        ]);

        $module = TeachingModule::create($validated);

        return redirect()->route('curriculum.modules.show', $module->id)
            ->with('success', 'Modul Ajar Kurikulum Merdeka berhasil di-generate & disimpan!');
    }

    public function show(TeachingModule $module)
    {
        $module->load(['subject', 'teacher', 'schoolClass', 'learningOutcome', 'learningObjective']);
        $school = School::first();

        return view('curriculum.modules.show', compact('module', 'school'));
    }

    public function edit(TeachingModule $module)
    {
        $module->load(['subject', 'teacher', 'schoolClass', 'learningOutcome', 'learningObjective']);

        $user = Auth::user();
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

            $teachers = collect([$teacher]);
            $learningOutcomes = LearningOutcome::where('status', 'active')
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->get();
            $learningObjectives = LearningObjective::where('status', 'active')
                ->whereIn('learning_outcome_id', $learningOutcomes->pluck('id'))
                ->get();
        } else {
            $subjects = Subject::where('status', 'active')->get();
            $teachers = Teacher::all();
            $classes = SchoolClass::all();
            $learningOutcomes = LearningOutcome::where('status', 'active')->get();
            $learningObjectives = LearningObjective::where('status', 'active')->get();
        }

        return view('curriculum.modules.edit', compact('module', 'subjects', 'teachers', 'classes', 'learningOutcomes', 'learningObjectives', 'isGuru', 'teacher'));
    }

    public function update(Request $request, TeachingModule $module)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'nullable|exists:classes,id',
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'title' => 'required|string|max:255',
            'phase' => 'required|in:E,F',
            'grade_level' => 'required|in:X,XI,XII',
            'allocated_hours' => 'required|integer|min:1',
            'learning_model' => 'required|string|max:100',
            'methods' => 'required|string|max:255',
            'target_students' => 'required|string|max:100',
            'preliminary_activities' => 'required|string',
            'core_activities' => 'required|string',
            'closing_activities' => 'required|string',
            'diagnostic_assessment' => 'nullable|string',
            'formative_assessment' => 'nullable|string',
            'summative_assessment' => 'nullable|string',
            'remedial_plan' => 'nullable|string',
            'enrichment_plan' => 'nullable|string',
            'student_worksheet' => 'nullable|string',
            'assessment_rubric' => 'nullable|string',
        ]);

        $module->update($validated);

        return redirect()->route('curriculum.modules.show', $module->id)
            ->with('success', 'Modul Ajar ' . $module->title . ' berhasil diperbarui!');
    }

    public function print(TeachingModule $module)
    {
        $module->load(['subject', 'teacher', 'schoolClass', 'learningOutcome', 'learningObjective']);
        $school = School::first();

        return view('curriculum.modules.print', compact('module', 'school'));
    }

    public function destroy(TeachingModule $module)
    {
        $module->delete();
        return redirect()->route('curriculum.modules.index')->with('success', 'Modul Ajar berhasil dihapus.');
    }
}
