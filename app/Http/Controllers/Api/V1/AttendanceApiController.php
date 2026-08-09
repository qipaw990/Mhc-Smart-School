<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\QrAttendanceSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
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
                    'date'               => $date,
                    'has_attended'       => $attendance !== null,
                    'attendance_status'  => $attendance?->status,
                    'status_label'       => $attendance?->status_label ?? 'Belum Presensi',
                    'time'               => $attendance?->time,
                    'method'             => $attendance?->method,
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
     * Scan QR Code from Android App
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
            'school_id'        => $school->id,
            'academic_year_id' => $ay->id,
            'teacher_id'       => $session->teacher_id,
            'time'             => now()->toTimeString(),
            'type'             => 'subject_session',
            'method'           => 'qr_dynamic_mobile',
            'status'           => $autoStatus,
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'device_info'      => $request->userAgent() ?? 'Android Mobile App',
        ]);

        // Send WhatsApp Notification
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
}
