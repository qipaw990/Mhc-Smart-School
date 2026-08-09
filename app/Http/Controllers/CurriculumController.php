<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\Subject;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();

        $user = auth()->user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $activeScheduleId = \App\Models\Schedule::where('status', 'active')->value('id');

        if ($isGuru && $teacher) {
            $scheduleSubjects = \App\Models\ScheduleItem::where('teacher_id', $teacher->id)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with('subject')
                ->get()
                ->pluck('subject')
                ->filter();

            $loadSubjects = $teacher->teachingLoads()->with('subject')->get()->pluck('subject')->filter();

            $subjects = $scheduleSubjects->merge($loadSubjects)->unique('id')->sortBy('name')->values();
        } else {
            $subjects = Subject::where('status', 'active')->get();
        }

        $selectedSubjectId = $request->get('subject_id', $subjects->first()?->id);
        $selectedSubject = null;
        if ($selectedSubjectId) {
            $selectedSubject = Subject::with(['learningOutcomes.learningObjectives.materials'])->find($selectedSubjectId);
        }

        return view('curriculum.cp_tp', compact('subjects', 'selectedSubject', 'ay', 'isGuru', 'teacher'));
    }

    public function storeCp(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'phase' => 'required|in:E,F',
            'code' => 'required|string|max:30',
            'element' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ay = AcademicYear::where('is_active', true)->first();

        LearningOutcome::create(array_merge($validated, [
            'academic_year_id' => $ay?->id,
            'status' => 'active',
        ]));

        return back()->with('success', 'Capaian Pembelajaran (CP) baru berhasil ditambahkan!');
    }

    public function updateCp(Request $request, LearningOutcome $learningOutcome)
    {
        $validated = $request->validate([
            'phase' => 'required|in:E,F',
            'code' => 'required|string|max:30',
            'element' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $learningOutcome->update($validated);

        return back()->with('success', 'Capaian Pembelajaran ' . $learningOutcome->code . ' berhasil diperbarui!');
    }

    public function destroyCp(LearningOutcome $learningOutcome)
    {
        $learningOutcome->delete();
        return back()->with('success', 'Capaian Pembelajaran berhasil dihapus.');
    }

    public function storeTp(Request $request)
    {
        $validated = $request->validate([
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'code' => 'required|string|max:30',
            'order_number' => 'required|integer|min:1',
            'description' => 'required|string',
            'semester_number' => 'required|in:1,2',
            'estimated_hours' => 'required|integer|min:1',
        ]);

        LearningObjective::create(array_merge($validated, [
            'status' => 'active',
        ]));

        return back()->with('success', 'Tujuan Pembelajaran (TP) berhasil ditambahkan!');
    }

    public function updateTp(Request $request, LearningObjective $learningObjective)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30',
            'order_number' => 'required|integer|min:1',
            'description' => 'required|string',
            'semester_number' => 'required|in:1,2',
            'estimated_hours' => 'required|integer|min:1',
        ]);

        $learningObjective->update($validated);

        return back()->with('success', 'Tujuan Pembelajaran ' . $learningObjective->code . ' berhasil diperbarui!');
    }

    public function destroyTp(LearningObjective $learningObjective)
    {
        $learningObjective->delete();
        return back()->with('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }
}
