<?php

namespace App\Http\Controllers;

use App\Models\LearningObjective;
use App\Models\Material;
use App\Models\Subject;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
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

        return view('curriculum.materials', compact('subjects', 'selectedSubject', 'isGuru', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'external_link' => 'nullable|url',
            'estimated_minutes' => 'required|integer|min:1',
            'sequence_order' => 'required|integer|min:1',
        ]);

        Material::create($validated);

        return back()->with('success', 'Materi Pembelajaran berhasil ditambahkan ke Tujuan Pembelajaran!');
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'external_link' => 'nullable|url',
            'estimated_minutes' => 'required|integer|min:1',
            'sequence_order' => 'required|integer|min:1',
        ]);

        $material->update($validated);

        return back()->with('success', 'Materi Pembelajaran ' . $material->title . ' berhasil diperbarui!');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return back()->with('success', 'Materi Pembelajaran berhasil dihapus.');
    }
}
