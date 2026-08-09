<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ScheduleItem;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load(['roles', 'teacher', 'student']);

        $role = $user->roles->first()?->name ?? 'siswa';
        $todayStr = now()->toDateString();
        $dayName = match (now()->dayOfWeekIso) {
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu', default => 'Senin'
        };

        $school = School::first();

        // 1. General Metrics
        $totalStudents = Student::where('status', 'active')->count();
        $totalTeachers = Teacher::count();
        $totalClasses  = SchoolClass::count();
        $totalSubjects = Subject::count();

        // 2. Attendance Summary Today
        $presentToday = Attendance::where('date', $todayStr)->whereIn('status', ['H', 'T'])->count();
        $absentToday  = Attendance::where('date', $todayStr)->where('status', 'A')->count();

        // 3. User Specific Schedule Today
        $scheduleToday = [];
        if ($user->teacher) {
            $scheduleToday = ScheduleItem::with(['subject', 'schoolClass', 'room'])
                ->where('teacher_id', $user->teacher->id)
                ->where('day', $dayName)
                ->orderBy('period')
                ->get();
        } elseif ($user->student && $user->student->current_class_id) {
            $scheduleToday = ScheduleItem::with(['subject', 'teacher', 'room'])
                ->where('class_id', $user->student->current_class_id)
                ->where('day', $dayName)
                ->orderBy('period')
                ->get();
        }

        // 4. Active CBT Exams
        $activeExams = Exam::with('subject')
            ->where('status', 'published')
            ->orderBy('start_time', 'desc')
            ->take(5)
            ->get();

        // 5. Recent Journals
        $recentJournals = TeachingJournal::with(['teacher', 'subject', 'schoolClass'])
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'school'          => [
                    'name' => $school?->name,
                    'npsn' => $school?->npsn,
                ],
                'role'            => $role,
                'day_name'        => $dayName,
                'date'            => $todayStr,
                'metrics'         => [
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_classes'  => $totalClasses,
                    'total_subjects' => $totalSubjects,
                    'present_today'  => $presentToday,
                    'absent_today'   => $absentToday,
                ],
                'schedule_today'  => $scheduleToday,
                'active_exams'    => $activeExams,
                'recent_journals' => $recentJournals,
            ],
        ]);
    }
}
