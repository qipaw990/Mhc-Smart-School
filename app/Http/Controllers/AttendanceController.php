<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\QrAttendanceSession;
use App\Models\ScheduleItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\WaLog;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::all();
        $date = $request->get('date', now()->toDateString());
        $selectedClassId = $request->get('class_id', $classes->first()?->id);

        $selectedClass = SchoolClass::with('students')->find($selectedClassId);

        $attendances = Attendance::with(['student', 'recordedByTeacher', 'scheduleItem.subject'])
            ->where('date', $date)
            ->when($selectedClassId, function ($q) use ($selectedClassId) {
                $q->whereHas('student', fn($sq) => $sq->where('current_class_id', $selectedClassId));
            })
            ->get();

        // Statistics
        $totalStudents = $selectedClass?->students->count() ?? 0;
        $presentCount  = $attendances->where('status', 'H')->count();
        $sickCount     = $attendances->where('status', 'S')->count();
        $permitCount   = $attendances->where('status', 'I')->count();
        $absentCount   = $attendances->where('status', 'A')->count();
        $lateCount     = $attendances->where('status', 'T')->count();

        return view('attendance.index', compact(
            'classes',
            'selectedClass',
            'date',
            'attendances',
            'totalStudents',
            'presentCount',
            'sickCount',
            'permitCount',
            'absentCount',
            'lateCount',
            'ay'
        ));
    }

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

        $logs = $query->latest()->paginate(20);

        // Stats
        $totalSent   = WaLog::where('status', 'success')->count();
        $totalFailed = WaLog::where('status', 'failed')->count();
        $todayCount  = WaLog::whereDate('created_at', now()->toDateString())->count();

        return view('attendance.wa_logs', compact('logs', 'totalSent', 'totalFailed', 'todayCount'));
    }

    public function printMonthlyReport(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();
        $selectedClassId = $request->get('class_id', $classes->first()?->id);
        $selectedClass   = SchoolClass::with(['students' => fn($q) => $q->orderBy('name')])->find($selectedClassId);

        $monthStr = $request->get('month', now()->format('Y-m'));
        $parts    = explode('-', $monthStr);
        $year     = (int) ($parts[0] ?? now()->year);
        $month    = (int) ($parts[1] ?? now()->month);

        $dateCarbon  = \Carbon\Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $dateCarbon->daysInMonth;
        $monthName   = $dateCarbon->locale('id')->isoFormat('MMMM Y');

        $school = School::first();

        // Fetch attendances for students in this class for the selected month
        $students   = $selectedClass?->students ?? collect();
        $studentIds = $students->pluck('id')->toArray();

        $rawAttendances = Attendance::whereIn('student_id', $studentIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        // Pre-index by "student_id|Y-m-d" to avoid Carbon vs string comparison bug.
        // $att->date is cast to Carbon, so firstWhere('date', string) always misses.
        $indexed = [];
        foreach ($rawAttendances as $att) {
            $key = $att->student_id . '|' . $att->date->format('Y-m-d');
            // Keep last record if duplicate date (e.g. multiple sessions same day)
            $indexed[$key] = $att->status;
        }

        // Build matrix per student
        $matrix = [];
        foreach ($students as $student) {
            $days = [];
            $countH = 0; $countS = 0; $countI = 0; $countA = 0; $countT = 0;

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

            $totalActiveDays = $countH + $countS + $countI + $countA + $countT;
            $percent = $totalActiveDays > 0 ? round((($countH + $countT) / $totalActiveDays) * 100) : 100;

            $matrix[$student->id] = [
                'student'    => $student,
                'days'       => $days,
                'count_h'    => $countH,
                'count_s'    => $countS,
                'count_i'    => $countI,
                'count_a'    => $countA,
                'count_t'    => $countT,
                'percentage' => $percent,
            ];
        }

        return view('attendance.monthly_report', compact(
            'classes', 'selectedClass', 'selectedClassId',
            'monthStr', 'monthName', 'daysInMonth', 'year', 'month',
            'school', 'matrix'
        ));
    }

    public function qrScanner(Request $request)
    {
        $scheduleItems = ScheduleItem::with(['teacher', 'subject', 'schoolClass', 'room'])
            ->where('day', $this->getIndonesianDayName(now()->dayOfWeekIso))
            ->get();

        $selectedItemId = $request->get('schedule_item_id', $scheduleItems->first()?->id);
        $selectedItem   = ScheduleItem::with(['teacher', 'subject', 'schoolClass', 'room'])->find($selectedItemId);

        // Generate / Refresh Dynamic QR Token (Valid for 30 seconds)
        $token = null;
        if ($selectedItem) {
            $token = 'MHC-' . strtoupper(Str::random(12)) . '-' . time();
            QrAttendanceSession::create([
                'schedule_item_id'   => $selectedItem->id,
                'teacher_id'         => $selectedItem->teacher_id,
                'token'              => $token,
                'expires_at'         => now()->addSeconds(30),
                'refresh_interval_sec' => 15,
            ]);
        }

        return view('attendance.qr', compact('scheduleItems', 'selectedItem', 'token'));
    }

    /**
     * Metode 1: Siswa scan QR guru (QR dinamis di layar)
     */
    public function recordQrScan(Request $request)
    {
        $validated = $request->validate([
            'token'      => 'required|string',
            'student_id' => 'required|exists:students,id',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ]);

        $session = QrAttendanceSession::where('token', $validated['token'])
            ->where('expires_at', '>=', now())
            ->first();

        if (! $session) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token QR Code tidak valid atau sudah kedaluwarsa. Silakan scan ulang QR yang aktif di layar guru.',
            ], 422);
        }

        $student    = Student::with(['currentClass', 'user'])->find($validated['student_id']);
        $school     = School::first();
        $ay         = AcademicYear::where('is_active', true)->first();

        $timeLateSetting = \App\Models\Setting::get('attendance_time_late', '07:15');
        $autoStatus      = (now()->format('H:i') > $timeLateSetting) ? 'T' : 'H';

        $attendance = Attendance::updateOrCreate([
            'date'             => now()->toDateString(),
            'schedule_item_id' => $session->schedule_item_id,
            'student_id'       => $validated['student_id'],
        ], [
            'school_id'        => $school->id,
            'academic_year_id' => $ay->id,
            'teacher_id'       => $session->teacher_id,
            'time'             => now()->toTimeString(),
            'type'             => 'subject_session',
            'method'           => 'qr_dynamic',
            'status'           => $autoStatus,
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'device_info'      => $request->userAgent(),
        ]);

        // Kirim notifikasi WhatsApp
        $this->sendWaAttendanceNotif($student, $attendance);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Presensi berhasil dicatat!',
            'student_name' => $student?->name,
            'data'         => $attendance,
        ]);
    }

    /**
     * Metode 2: Guru scan QR kartu pelajar siswa
     * QR berisi NISN siswa
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

        if (! $student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Siswa dengan NISN ' . $validated['nisn'] . ' tidak ditemukan.',
            ], 404);
        }

        $school    = School::first();
        $ay        = AcademicYear::where('is_active', true)->first();
        $teacherId = Auth::user()?->teacher?->id;

        $baseData = [
            'date'       => now()->toDateString(),
            'student_id' => $student->id,
        ];

        if (! empty($validated['schedule_item_id'])) {
            $baseData['schedule_item_id'] = $validated['schedule_item_id'];
        }

        $timeLateSetting = \App\Models\Setting::get('attendance_time_late', '07:15');
        $autoStatus      = (now()->format('H:i') > $timeLateSetting) ? 'T' : 'H';

        $attendance = Attendance::updateOrCreate($baseData, [
            'school_id'        => $school->id,
            'academic_year_id' => $ay->id,
            'teacher_id'       => $teacherId,
            'time'             => now()->toTimeString(),
            'type'             => 'subject_session',
            'method'           => 'qr_card',
            'status'           => $autoStatus,
            'latitude'         => $validated['latitude'] ?? null,
            'longitude'        => $validated['longitude'] ?? null,
            'device_info'      => $request->userAgent(),
        ]);

        // Kirim notifikasi WhatsApp
        $this->sendWaAttendanceNotif($student, $attendance);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Presensi ' . $student->name . ' berhasil dicatat!',
            'student_name' => $student->name,
            'student_nisn' => $student->nisn,
            'class'        => $student->currentClass?->name ?? '-',
            'photo'        => $student->photo ? asset('storage/' . $student->photo) : null,
            'data'         => $attendance,
        ]);
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'date'      => 'required|date',
            'class_id'  => 'required|exists:classes,id',
            'statuses'  => 'required|array',
            'statuses.*' => 'required|in:H,S,I,A,T,D,P',
        ]);

        $school    = School::first();
        $ay        = AcademicYear::where('is_active', true)->first();
        $teacherId = Auth::user()?->teacher?->id;

        $waMessages = [];

        foreach ($validated['statuses'] as $studentId => $status) {
            Attendance::updateOrCreate([
                'date'       => $validated['date'],
                'student_id' => $studentId,
            ], [
                'school_id'        => $school->id,
                'academic_year_id' => $ay->id,
                'time'             => now()->toTimeString(),
                'type'             => 'daily',
                'method'           => 'manual',
                'status'           => $status,
                'teacher_id'       => $teacherId,
            ]);

            // Kumpulkan pesan WA untuk kirim bulk
            $student = Student::with(['currentClass'])->find($studentId);
            if ($student) {
                $waPayloads = $this->buildWaMessages($student, $status, $validated['date'], now()->toTimeString());
                $waMessages = array_merge($waMessages, $waPayloads);
            }
        }

        // Kirim WA bulk ke semua orang tua
        if (! empty($waMessages)) {
            try {
                app(WhatsAppService::class)->sendBulk($waMessages);
            } catch (\Throwable $e) {
                // Silent fail — absensi tetap tersimpan
            }
        }

        return back()->with('success', 'Presensi kelas tanggal ' . $validated['date'] . ' berhasil disimpan!');
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function sendWaAttendanceNotif(Student $student, Attendance $attendance): void
    {
        try {
            $wa = app(WhatsAppService::class);
            $wa->sendAttendanceNotification(
                studentName: $student->name,
                className:   $student->currentClass?->name ?? '-',
                statusCode:  $attendance->status,
                date:        $attendance->date,
                time:        $attendance->time,
                phone:       $student->phone,
                parentPhone: $student->parent_phone ?? null,
            );
        } catch (\Throwable $e) {
            // Silent — jangan gagalkan presensi karena WA error
        }
    }

    private function buildWaMessages(Student $student, string $status, string $date, string $time): array
    {
        $statusLabel = match ($status) {
            'H' => '✅ HADIR',
            'T' => '⏰ TERLAMBAT',
            'S' => '🏥 SAKIT',
            'I' => '📋 IZIN',
            'A' => '❌ ALPA (TIDAK HADIR)',
            'D' => '🏠 DISPEN',
            'P' => '🏕️ PRAKERIN',
            default => $status,
        };

        $dateFormatted = \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM Y');
        $className     = $student->currentClass?->name ?? '-';

        $message = implode("\n", [
            '🏫 *NOTIFIKASI PRESENSI*',
            '*SMK MUTHIA HARAPAN CICALENGKA*',
            str_repeat('─', 30),
            "👤 Nama    : {$student->name}",
            "🏫 Kelas   : {$className}",
            "📊 Status  : {$statusLabel}",
            "📅 Tanggal : {$dateFormatted}",
            "🕐 Waktu   : {$time}",
            str_repeat('─', 30),
            '_Pesan ini dikirim otomatis oleh sistem MHC Smart School._',
        ]);

        $targets = [];
        if (! empty($student->phone)) {
            $targets[] = ['phone' => $student->phone, 'message' => $message];
        }
        if (! empty($student->parent_phone) && $student->parent_phone !== $student->phone) {
            $targets[] = ['phone' => $student->parent_phone, 'message' => $message];
        }

        return $targets;
    }

    private function getIndonesianDayName(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => 'Senin',
        };
    }
}
