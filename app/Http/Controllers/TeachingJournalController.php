<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\ScheduleItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeachingJournalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $query = TeachingJournal::with(['teacher', 'schoolClass', 'subject', 'learningObjective'])
            ->orderBy('date', 'desc')
            ->orderBy('period_start', 'desc');

        if ($isGuru && $teacher) {
            $query->where('teacher_id', $teacher->id);
        } elseif ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $journals = $query->paginate(15);
        $classes = SchoolClass::all();
        $teachers = Teacher::all();

        return view('journals.index', compact('journals', 'classes', 'teachers', 'isGuru', 'teacher'));
    }

    public function create(Request $request)
    {
        $user    = Auth::user();
        $isGuru  = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        $activeScheduleId = \App\Models\Schedule::where('status', 'active')->value('id');

        if ($isGuru && $teacher) {
            // Scope everything to teacher's own schedule slots
            $mySlots = ScheduleItem::where('teacher_id', $teacher->id)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with(['subject', 'schoolClass', 'room'])
                ->get();

            $subjects   = $mySlots->pluck('subject')->filter()->unique('id')->sortBy('name')->values();
            $classes    = $mySlots->pluck('schoolClass')->filter()->unique('id')->sortBy('name')->values();
            $teachers   = collect([$teacher]); // only themselves
            $scheduleItems = $mySlots;

            $learningObjectives = LearningObjective::where('status', 'active')
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->get();
        } else {
            $teachers   = Teacher::all();
            $classes    = SchoolClass::all();
            $subjects   = Subject::where('status', 'active')->get();
            $scheduleItems = ScheduleItem::with(['teacher', 'subject', 'schoolClass', 'room'])->get();
            $learningObjectives = LearningObjective::where('status', 'active')->get();
        }

        return view('journals.create', compact('teachers', 'classes', 'subjects', 'learningObjectives', 'scheduleItems', 'isGuru', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'learning_objective_id' => 'nullable|exists:learning_objectives,id',
            'schedule_item_id' => 'nullable|exists:schedule_items,id',
            'date' => 'required|date',
            'period_start' => 'required|integer|min:1|max:10',
            'period_end' => 'required|integer|min:1|max:10',
            'topic_activity' => 'required|string',
            'notes_challenges' => 'nullable|string',
            'student_present_count' => 'required|integer|min:0',
            'student_absent_count' => 'required|integer|min:0',
        ]);

        $school = School::first();

        TeachingJournal::create(array_merge($validated, [
            'school_id' => $school->id,
            'status' => 'submitted',
        ]));

        return redirect()->route('journals.index')->with('success', 'Jurnal Mengajar berhasil dicatat dan disimpan!');
    }

    public function show(TeachingJournal $journal)
    {
        $journal->load(['teacher', 'schoolClass', 'subject', 'learningObjective', 'scheduleItem.room']);
        $school = School::first();

        return view('journals.show', compact('journal', 'school'));
    }

    public function destroy(TeachingJournal $journal)
    {
        $journal->delete();
        return back()->with('success', 'Jurnal mengajar berhasil dihapus.');
    }
}
