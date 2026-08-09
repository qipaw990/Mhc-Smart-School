<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingLoad;
use App\Models\TimeSlot;
use App\Services\SchedulerEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchedulerController extends Controller
{
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $activeSchedule = Schedule::where('status', 'active')->first();

        $user = Auth::user();
        $isGuru = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;

        if ($isGuru && $teacher) {
            // Teacher: always lock to their own schedule, no filter bar
            $selectedTeacherId = $teacher->id;
            $selectedClassId   = null;
            $selectedRoomId    = null;

            $items = ScheduleItem::with(['teacher', 'subject', 'schoolClass', 'room', 'timeSlot'])
                ->where('schedule_id', $activeSchedule?->id)
                ->where('teacher_id', $teacher->id)
                ->get()
                ->groupBy(fn($item) => $item->day . '_' . $item->period);

            return view('scheduler.index', [
                'activeSchedule'   => $activeSchedule,
                'items'            => $items,
                'isGuru'           => true,
                'teacher'          => $teacher,
                'selectedTeacherId'=> $selectedTeacherId,
                'selectedClassId'  => null,
                'selectedRoomId'   => null,
                'classes'          => collect(),
                'teachers'         => collect(),
                'rooms'            => collect(),
                'timeSlots'        => collect(),
                'ay'               => $ay,
            ]);
        }

        // Admin / Kepala Sekolah path — full filter bar
        $classes   = SchoolClass::all();
        $teachers  = Teacher::all();
        $rooms     = Room::all();
        $timeSlots = TimeSlot::orderBy('day')->orderBy('period')->get();

        $selectedClassId   = $request->get('class_id');
        $selectedTeacherId = $request->get('teacher_id');
        $selectedRoomId    = $request->get('room_id');

        $query = ScheduleItem::with(['teacher', 'subject', 'schoolClass', 'room', 'timeSlot'])
            ->where('schedule_id', $activeSchedule?->id);

        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        } elseif ($selectedTeacherId) {
            $query->where('teacher_id', $selectedTeacherId);
        } elseif ($selectedRoomId) {
            $query->where('room_id', $selectedRoomId);
        } else {
            $firstClass = $classes->first();
            if ($firstClass) {
                $selectedClassId = $firstClass->id;
                $query->where('class_id', $firstClass->id);
            }
        }

        $items = $query->get()->groupBy(fn($item) => $item->day . '_' . $item->period);

        return view('scheduler.index', compact(
            'activeSchedule',
            'classes',
            'teachers',
            'rooms',
            'timeSlots',
            'items',
            'selectedClassId',
            'selectedTeacherId',
            'selectedRoomId',
            'ay',
            'isGuru',
            'teacher'
        ));
    }

    public function teachingLoads(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $loads = TeachingLoad::with(['teacher', 'subject', 'schoolClass', 'preferredRoom'])
            ->where('academic_year_id', $ay?->id)
            ->paginate(15);

        $teachers = Teacher::all();
        $subjects = Subject::where('status', 'active')->get();
        $classes = SchoolClass::all();
        $rooms = Room::all();

        // Summary of Teacher Loads
        $teacherSummaries = Teacher::with(['teachingLoads' => fn($q) => $q->where('academic_year_id', $ay?->id)])
            ->get()
            ->map(function ($teacher) {
                $totalHours = $teacher->teachingLoads->sum('hours_per_week');
                return [
                    'teacher' => $teacher,
                    'total_hours' => $totalHours,
                    'target_hours' => 24, // Standard 24 JP/minggu
                    'status' => $totalHours >= 24 ? 'CUKUP' : 'KURANG',
                ];
            });

        return view('scheduler.loads', compact('loads', 'teachers', 'subjects', 'classes', 'rooms', 'teacherSummaries', 'ay'));
    }

    public function storeTeachingLoad(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'hours_per_week' => 'required|integer|min:1',
            'preferred_room_id' => 'nullable|exists:rooms,id',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();

        TeachingLoad::create(array_merge($validated, [
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
        ]));

        return back()->with('success', 'Beban Mengajar guru berhasil ditambahkan!');
    }

    public function destroyTeachingLoad(TeachingLoad $load)
    {
        $load->delete();
        return back()->with('success', 'Beban Mengajar berhasil dihapus.');
    }

    public function generator()
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $schedules = Schedule::where('academic_year_id', $ay?->id)
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSchedule = $schedules->firstWhere('status', 'active');
        $totalLoads = TeachingLoad::where('academic_year_id', $ay?->id)->sum('hours_per_week');

        return view('scheduler.generator', compact('schedules', 'activeSchedule', 'totalLoads', 'ay'));
    }

    public function runGenerator(Request $request, SchedulerEngineService $engine)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $scheduleName = $request->input('schedule_name', 'Jadwal Otomatis Regular');

        $result = $engine->generateSchedule($ay->id, null, $scheduleName, Auth::id());

        if ($result['status'] === 'error') {
            return back()->withErrors(['error' => $result['message']]);
        }

        return redirect()->route('scheduler.index')->with('success', 
            'Jadwal Otomatis berhasil digenerate! Optimization Score: ' . $result['optimization_score'] . '% (' . $result['total_items'] . ' sesi terdistribusi).'
        );
    }

    public function conflicts(SchedulerEngineService $engine)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $activeSchedule = Schedule::where('academic_year_id', $ay?->id)
            ->where('status', 'active')
            ->first();

        $diagnostic = null;
        if ($activeSchedule) {
            $diagnostic = $engine->detectConflicts($activeSchedule->id);
        }

        return view('scheduler.conflicts', compact('activeSchedule', 'diagnostic', 'ay'));
    }
}
