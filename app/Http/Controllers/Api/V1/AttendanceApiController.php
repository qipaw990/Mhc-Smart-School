<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\QrAttendanceSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\WaLog;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Get Today Attendance Status for Logged-In User
     */
    public function today(Request $request)
    {
        $user    = $request->user();
        $date    = now()->toDateString();
        $student = $user->student;

        if ($student) {
            $attendance = Attendance::where('student_id', $student->id)
                ->where('date', $date)
                ->first();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'date'              => $date,
                    'has_attended'      => $attendance !== null,
                    'attendance_status' => $attendance?->status,
                    'status_label'      => $attendance?->status_label ?? 'Belum Presensi',
                    'time'              => $attendance?->time,
                    'method'            => $attendance?->method,
                ],
            ]);
        }

        // For Teacher / Admin: summary of today's total attendance
        $totalStudents = Student::where('status', 'active')->count();
        $presentToday  = Attendance::where('date', $date)->whereIn('status', ['H', 'T'])->count();
        $absentToday   = Attendance::where('date', $date)->where('status', 'A')->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'date'           => $date,
                'total_students' => $totalStudents,
                'present_today'  => $presentToday,
                'absent_today'   => $absentToday,
            ],
        ]);
    }

    /**
     * Scan QR Code from Android App (Student scanning teacher QR)
     */
    public function scanQr(Request $request)
    {
        $validated = $request->validate([
            'token'     => 'required|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ], [
            'token.required' => 'Token QR Code wajib dikirim.',
        ]);

        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya akun siswa yang dapat melakukan presensi scan QR.',
            ], 403);
        }

        $session = QrAttendanceSession::where('token', $validated['token'])
            ->where('expires_at', '>=', now())
            ->first();

        if (!$session) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token QR Code tidak valid atau sudah kedaluwarsa. Silakan scan QR aktif.',
            ], 422);
        }

        $school = School::first();
        $ay     = AcademicYear::where('is_active', true)->first();

        $timeLateSetting = \App\Models\Setting::get('attendance_time_late', '07:15');
        $autoStatus      = (now()->format('H:i') > $timeLateSetting) ? 'T' : 'H';

        $attendance = Attendance::updateOrCreate([
            'date'             => now()->toDateString(),
            'schedule_item_id' => $session->schedule_item_id,
            'student_id'       => $student->id,
        ], [
            'school_id'        => $school?->id,
            'academic_year_id' => $ay?->id,
            'teacher_id'       => $session->teacher_id,
            'time'             => now()->toTimeString(),
            'type'             => 'subject_session',
            'method'           => 'qr_dynamic_mobile',
            'status'           => $autoStatus,
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'device_info'      => $request->userAgent() ?? 'Android Mobile App',
        ]);

        try {
            app(WhatsAppService::class)->sendAttendanceNotification(
                studentName: $student->name,
                className:   $student->currentClass?->name ?? '-',
                statusCode:  $attendance->status,
                date:        $attendance->date,
                time:        $attendance->time,
                phone:       $student->phone,
                parentPhone: $student->parent_phone ?? null,
            );
        } catch (\Throwable $e) {
            // Ignore WA error
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Presensi berhasil dicatat via Mobile App!',
            'data'    => [
                'student_name' => $student->name,
                'status'       => $attendance->status,
                'status_label' => $attendance->status_label,
                'time'         => $attendance->time,
            ],
        ]);
    }

    /**
     * Teacher scanning Student QR ID Card (NISN)
     */
    public function scanStudentQr(Request $request)
    {
        $validated = $request->validate([
            'nisn'             => 'required|string',
            'schedule_item_id' => 'nullable|exists:schedule_items,id',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
        ]);

        $student = Student::with(['currentClass', 'user'])
            ->where('nisn', $validated['nisn'])
            ->first();

        if (!$student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Siswa dengan NISN ' . $validated['nisn'] . ' tidak ditemukan.',
            ], 404);
        }

        $school    = School::first();
        $ay        = AcademicYear::where('is_active', true)->first();
        $teacherId = $request->user()->teacher?->id;

        $baseData = [
            'date'       => now()->toDateString(),
            'student_id' => $student->id,
        ];

        if (!empty($validated['schedule_item_id'])) {
            $baseData['schedule_item_id'] = $validated['schedule_item_id'];
        }

        $timeLateSetting = \App\Models\Setting::get('attendance_time_late', '07:15');
        $autoStatus      = (now()->format('H:i') > $timeLateSetting) ? 'T' : 'H';

        $attendance = Attendance::updateOrCreate($baseData, [
            'school_id'        => $school?->id,
            'academic_year_id' => $ay?->id,
            'teacher_id'       => $teacherId,
            'time'             => now()->toTimeString(),
            'type'             => 'subject_session',
            'method'           => 'qr_card_mobile',
            'status'           => $autoStatus,
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'device_info'      => $request->userAgent(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Presensi ' . $student->name . ' berhasil dicatat!',
            'data'    => [
                'student_name' => $student->name,
                'student_nisn' => $student->nisn,
                'class'        => $student->currentClass?->name ?? '-',
                'status'       => $attendance->status,
                'time'         => $attendance->time,
            ],
        ]);
    }

    /**
     * Store Manual Bulk Attendance for Class
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'class_id'   => 'required|exists:classes,id',
            'statuses'   => 'required|array',
            'statuses.*' => 'required|in:H,S,I,A,T,D,P',
        ]);

        $school    = School::first();
        $ay        = AcademicYear::where('is_active', true)->first();
        $teacherId = $request->user()->teacher?->id;

        foreach ($validated['statuses'] as $studentId => $status) {
            Attendance::updateOrCreate([
                'date'       => $validated['date'],
                'student_id' => $studentId,
            ], [
                'school_id'        => $school?->id,
                'academic_year_id' => $ay?->id,
                'time'             => now()->toTimeString(),
                'type'             => 'daily',
                'method'           => 'manual_mobile',
                'status'           => $status,
                'teacher_id'       => $teacherId,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Presensi kelas tanggal ' . $validated['date'] . ' berhasil disimpan!',
        ]);
    }

    /**
     * Monthly Report Matrix per Class
     */
    public function monthlyReport(Request $request)
    {
        $classId  = $request->input('class_id');
        $monthStr = $request->input('month', now()->format('Y-m'));

        $selectedClass = SchoolClass::with(['students' => fn($q) => $q->orderBy('name')])->find($classId);

        if (!$selectedClass) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kelas tidak ditemukan.',
            ], 404);
        }

        $parts    = explode('-', $monthStr);
        $year     = (int) ($parts[0] ?? now()->year);
        $month    = (int) ($parts[1] ?? now()->month);

        $students   = $selectedClass->students;
        $studentIds = $students->pluck('id')->toArray();

        $rawAttendances = Attendance::whereIn('student_id', $studentIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $indexed = [];
        foreach ($rawAttendances as $att) {
            $key = $att->student_id . '|' . $att->date->format('Y-m-d');
            $indexed[$key] = $att->status;
        }

        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $matrix = [];

        foreach ($students as $student) {
            $countH = 0; $countS = 0; $countI = 0; $countA = 0; $countT = 0;
            $days = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateDay = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $key = $student->id . '|' . $dateDay;
                $st  = $indexed[$key] ?? '-';
                $days[$d] = $st;

                match ($st) {
                    'H' => $countH++,
                    'S' => $countS++,
                    'I' => $countI++,
                    'A' => $countA++,
                    'T' => $countT++,
                    default => null,
                };
            }

            $totalActive = $countH + $countS + $countI + $countA + $countT;
            $percent = $totalActive > 0 ? round((($countH + $countT) / $totalActive) * 100) : 100;

            $matrix[] = [
                'student_id'   => $student->id,
                'student_name' => $student->name,
                'nisn'         => $student->nisn,
                'count_h'      => $countH,
                'count_s'      => $countS,
                'count_i'      => $countI,
                'count_a'      => $countA,
                'count_t'      => $countT,
                'percentage'   => $percent,
                'days'         => $days,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'class'         => $selectedClass->name,
                'month'         => $monthStr,
                'days_in_month' => $daysInMonth,
                'matrix'        => $matrix,
            ],
        ]);
    }

    /**
     * Get WA Notification Logs
     */
    public function waLogs(Request $request)
    {
        $query = WaLog::query();

        if ($request->filled('search')) {
            $query->search($request->get('search'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->get('date'));
        }

        $logs = $query->latest()->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }
}
