<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\Subject;
use Illuminate\Http\Request;

class AtpBuilderController extends Controller
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
            $selectedSubject = Subject::with(['learningOutcomes.learningObjectives'])->find($selectedSubjectId);
        }

        $learningPath = null;
        if ($selectedSubject && $ay) {
            $learningPath = LearningPath::with(['items.learningObjective.learningOutcome'])
                ->where('subject_id', $selectedSubject->id)
                ->where('academic_year_id', $ay->id)
                ->first();
        }

        return view('curriculum.atp_builder', compact('subjects', 'selectedSubject', 'learningPath', 'ay', 'isGuru', 'teacher'));
    }

    public function storeHeader(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'phase' => 'required|in:E,F',
            'semester_number' => 'required|in:1,2',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $ay = AcademicYear::where('is_active', true)->first();
        $subject = Subject::findOrFail($validated['subject_id']);

        $atp = LearningPath::updateOrCreate([
            'subject_id' => $subject->id,
            'academic_year_id' => $ay->id,
            'semester_number' => $validated['semester_number'],
        ], [
            'major_id' => $subject->major_id,
            'phase' => $validated['phase'],
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('curriculum.atp.index', ['subject_id' => $subject->id])
            ->with('success', 'Kerangka ATP berhasil disimpan! Silakan tambahkan urutan alur TP ke timeline.');
    }

    public function updateHeader(Request $request, LearningPath $learningPath)
    {
        $validated = $request->validate([
            'phase' => 'required|in:E,F',
            'semester_number' => 'required|in:1,2',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $learningPath->update($validated);

        return back()->with('success', 'Kerangka Dokumen ATP berhasil diperbarui!');
    }

    public function destroyHeader(LearningPath $learningPath)
    {
        $subjectId = $learningPath->subject_id;
        $learningPath->items()->delete();
        $learningPath->delete();

        return redirect()->route('curriculum.atp.index', ['subject_id' => $subjectId])
            ->with('success', 'Kerangka Dokumen ATP beserta seluruh alokasi timeline berhasil dihapus.');
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'learning_path_id' => 'required|exists:learning_paths,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'week_number' => 'required|integer|min:1|max:24',
            'hour_allocation' => 'required|integer|min:1',
            'topic' => 'required|string|max:255',
            'assessment_plan' => 'nullable|string',
        ]);

        $maxSeq = LearningPathItem::where('learning_path_id', $validated['learning_path_id'])->max('sequence_order') ?? 0;

        LearningPathItem::create(array_merge($validated, [
            'sequence_order' => $maxSeq + 1,
        ]));

        return back()->with('success', 'Langkah Tujuan Pembelajaran berhasil ditambahkan ke Timeline ATP!');
    }

    public function updateItem(Request $request, LearningPathItem $item)
    {
        $validated = $request->validate([
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'week_number' => 'required|integer|min:1|max:24',
            'hour_allocation' => 'required|integer|min:1',
            'topic' => 'required|string|max:255',
            'assessment_plan' => 'nullable|string',
        ]);

        $item->update($validated);

        return back()->with('success', 'Alokasi Minggu ke-' . $item->week_number . ' pada ATP berhasil diperbarui!');
    }

    public function deleteItem(LearningPathItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item ATP berhasil dihapus dari timeline.');
    }
}
