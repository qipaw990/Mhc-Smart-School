<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AuditLog;

use App\Models\Major;
use App\Models\Room;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $school = School::first();
        $activeAy = AcademicYear::where('is_active', true)->with('semesters')->first();
        $activeSemester = $activeAy?->semesters->where('is_active', true)->first();

        // System-wide counts
        $totalStudents = Student::where('status', 'active')->count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();
        $totalMajors = Major::count();
        $totalRooms = Room::count();

        // Sample attendance & analytics data
        $todayAttendanceRate = 96.8;
        $teacherAttendanceRate = 98.2;
        $averageGrade = 82.4;
        $atRiskCount = 2; // Andi Pratama sample risk
        $financialCollectionRate = 92.5;
        $teachingProgress = 87.0;

        $recentAuditLogs = AuditLog::orderBy('created_at', 'desc')->take(6)->get();

        // Teacher Personalized Data Binding
        $isTeacher = $user && $user->hasRole('guru') && !$user->hasRole(['super_admin', 'admin_sekolah']);
        $teacher = $user?->teacher;
        $teacherTeachingLoads = collect();
        $teacherTotalJp = 0;
        $teacherHomeroomClass = null;
        $teacherTodaySchedules = collect();
        $teacherRecentJournals = collect();
        $teacherRecentAssessments = collect();
        $teacherQuestionBanksCount = 0;

        if ($teacher) {
            // Get the active schedule
            $activeSchedule = \App\Models\Schedule::where('status', 'active')->first();
            $activeScheduleId = $activeSchedule?->id;

            // Calculate actual JP per week from schedule_items (what's shown in the timetable)
            $scheduleItems = \App\Models\ScheduleItem::where('teacher_id', $teacher->id)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with(['subject', 'schoolClass'])
                ->get();

            // Group schedule slots into unique (subject, class) pairs for the SK PBM table
            $teacherTeachingLoads = $scheduleItems
                ->groupBy(fn($i) => $i->subject_id . '_' . $i->class_id)
                ->map(function ($group) {
                    $first = $group->first();
                    return (object)[
                        'subject'        => $first->subject,
                        'schoolClass'    => $first->schoolClass,
                        'hours_per_week' => $group->count(), // actual JP = number of slots per week
                    ];
                })->values();

            $teacherTotalJp = $scheduleItems->count(); // total JP = total slots in active schedule

            $teacherHomeroomClass = $teacher->homeroomClasses()->with(['major', 'students'])->first();

            $dayMap = [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
                7 => 'Minggu',
            ];
            $todayName = $dayMap[date('N')] ?? 'Senin';

            $teacherTodaySchedules = \App\Models\ScheduleItem::where('teacher_id', $teacher->id)
                ->where('day', $todayName)
                ->when($activeScheduleId, fn($q) => $q->where('schedule_id', $activeScheduleId))
                ->with(['subject', 'schoolClass', 'room', 'timeSlot'])
                ->orderBy('period')
                ->get();

            $teacherRecentJournals = \App\Models\TeachingJournal::where('teacher_id', $teacher->id)
                ->with(['subject', 'schoolClass', 'learningObjective'])
                ->orderBy('date', 'desc')
                ->take(5)
                ->get();

            $teacherRecentAssessments = \App\Models\Assessment::where('teacher_id', $teacher->id)
                ->with(['subject', 'schoolClass', 'learningObjective'])
                ->orderBy('date', 'desc')
                ->take(5)
                ->get();

            $teacherQuestionBanksCount = \App\Models\QuestionBank::where('teacher_id', $teacher->id)->count();
        }

        return view('dashboard.index', compact(
            'user',
            'school',
            'activeAy',
            'activeSemester',
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalMajors',
            'totalRooms',
            'todayAttendanceRate',
            'teacherAttendanceRate',
            'averageGrade',
            'atRiskCount',
            'financialCollectionRate',
            'teachingProgress',
            'recentAuditLogs',
            'isTeacher',
            'teacher',
            'teacherTeachingLoads',
            'teacherTotalJp',
            'teacherHomeroomClass',
            'teacherTodaySchedules',
            'teacherRecentJournals',
            'teacherRecentAssessments',
            'teacherQuestionBanksCount'
        ));
    }
}
