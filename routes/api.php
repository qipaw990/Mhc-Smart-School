<?php

use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CbtApiController;
use App\Http\Controllers\Api\V1\CurriculumApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\GradebookApiController;
use App\Http\Controllers\Api\V1\JournalApiController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\P5ApiController;
use App\Http\Controllers\Api\V1\ReportCardApiController;
use App\Http\Controllers\Api\V1\ScheduleApiController;
use App\Http\Controllers\Api\V1\SchoolApiController;
use App\Http\Controllers\Api\V1\StudentPortalApiController;
use App\Http\Controllers\Api\V1\UserManagementApiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Android API Routes (V1) - MHC Smart School
|--------------------------------------------------------------------------
|
| Base URL: /api/v1
| 100% Feature Parity with Web Application
|
*/

Route::prefix('v1')->group(function () {

    // ── CORS Preflight - Catch All OPTIONS requests ────────────────
    Route::options('{any}', function () {
        return response('', 200);
    })->where('any', '.*');

    // ── Public Auth Endpoints ─────────────────────────────────────
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ── Protected Endpoints (Requires Bearer Token) ───────────────
    Route::middleware('auth:sanctum')->group(function () {

        // 1. Auth & User Profile Management
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/profile', [ProfileController::class, 'updateProfile']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

        // 2. Mobile Dashboard Overview
        Route::get('/dashboard', [DashboardApiController::class, 'index']);

        // 3. School Profile & WA Gateway Settings
        Route::get('/school', [SchoolApiController::class, 'show']);
        Route::put('/school', [SchoolApiController::class, 'update']);
        Route::post('/school/wa-settings', [SchoolApiController::class, 'updateWaSettings']);
        Route::post('/school/wa-template', [SchoolApiController::class, 'updateWaTemplate']);
        Route::post('/school/attendance-times', [SchoolApiController::class, 'updateAttendanceTimes']);
        Route::post('/school/wa-test', [SchoolApiController::class, 'sendTestWa']);

        // 4. Master Data (Kelas, Siswa, Guru, Mapel, Jurusan, Ruangan, Tahun Ajaran)
        Route::prefix('master')->group(function () {
            // Academic Year & Semester
            Route::get('/academic-year', [MasterDataApiController::class, 'academicYears']);
            Route::post('/academic-year', [MasterDataApiController::class, 'storeAcademicYear']);
            Route::post('/academic-year/{academicYear}/set-active', [MasterDataApiController::class, 'setActiveAcademicYear']);
            Route::post('/semester/{semester}/set-active', [MasterDataApiController::class, 'setActiveSemester']);

            // Classes
            Route::get('/classes', [MasterDataApiController::class, 'classes']);
            Route::post('/classes', [MasterDataApiController::class, 'storeClass']);
            Route::put('/classes/{class}', [MasterDataApiController::class, 'updateClass']);
            Route::delete('/classes/{class}', [MasterDataApiController::class, 'destroyClass']);

            // Students
            Route::get('/students', [MasterDataApiController::class, 'students']);
            Route::post('/students', [MasterDataApiController::class, 'storeStudent']);
            Route::put('/students/{student}', [MasterDataApiController::class, 'updateStudent']);
            Route::delete('/students/{student}', [MasterDataApiController::class, 'destroyStudent']);

            // Teachers
            Route::get('/teachers', [MasterDataApiController::class, 'teachers']);
            Route::post('/teachers', [MasterDataApiController::class, 'storeTeacher']);
            Route::put('/teachers/{teacher}', [MasterDataApiController::class, 'updateTeacher']);
            Route::delete('/teachers/{teacher}', [MasterDataApiController::class, 'destroyTeacher']);

            // Subjects
            Route::get('/subjects', [MasterDataApiController::class, 'subjects']);
            Route::post('/subjects', [MasterDataApiController::class, 'storeSubject']);
            Route::put('/subjects/{subject}', [MasterDataApiController::class, 'updateSubject']);
            Route::delete('/subjects/{subject}', [MasterDataApiController::class, 'destroySubject']);

            // Majors
            Route::get('/majors', [MasterDataApiController::class, 'majors']);
            Route::post('/majors', [MasterDataApiController::class, 'storeMajor']);
            Route::put('/majors/{major}', [MasterDataApiController::class, 'updateMajor']);
            Route::delete('/majors/{major}', [MasterDataApiController::class, 'destroyMajor']);

            // Rooms
            Route::get('/rooms', [MasterDataApiController::class, 'rooms']);
            Route::post('/rooms', [MasterDataApiController::class, 'storeRoom']);
            Route::put('/rooms/{room}', [MasterDataApiController::class, 'updateRoom']);
            Route::delete('/rooms/{room}', [MasterDataApiController::class, 'destroyRoom']);
        });

        // 5. Jadwal Pelajaran (Schedule)
        Route::prefix('schedule')->group(function () {
            Route::get('/my-schedule', [ScheduleApiController::class, 'mySchedule']);
            Route::get('/matrix', [ScheduleApiController::class, 'matrix']);
        });

        // 6. Kurikulum Merdeka (CP/TP, ATP, Bahan Ajar, Modul Ajar)
        Route::prefix('curriculum')->group(function () {
            // CP & TP
            Route::get('/outcomes', [CurriculumApiController::class, 'learningOutcomes']);
            Route::post('/cp', [CurriculumApiController::class, 'storeCp']);
            Route::put('/cp/{learningOutcome}', [CurriculumApiController::class, 'updateCp']);
            Route::delete('/cp/{learningOutcome}', [CurriculumApiController::class, 'destroyCp']);
            Route::post('/tp', [CurriculumApiController::class, 'storeTp']);
            Route::put('/tp/{learningObjective}', [CurriculumApiController::class, 'updateTp']);
            Route::delete('/tp/{learningObjective}', [CurriculumApiController::class, 'destroyTp']);

            // ATP Builder
            Route::get('/atp', [CurriculumApiController::class, 'atpIndex']);
            Route::post('/atp/header', [CurriculumApiController::class, 'storeAtpHeader']);
            Route::post('/atp/item', [CurriculumApiController::class, 'addAtpItem']);
            Route::delete('/atp/item/{item}', [CurriculumApiController::class, 'deleteAtpItem']);

            // Materials / Bahan Ajar
            Route::get('/materials', [CurriculumApiController::class, 'materials']);
            Route::post('/materials', [CurriculumApiController::class, 'storeMaterial']);
            Route::put('/materials/{material}', [CurriculumApiController::class, 'updateMaterial']);
            Route::delete('/materials/{material}', [CurriculumApiController::class, 'destroyMaterial']);

            // Teaching Modules / Modul Ajar
            Route::get('/modules', [CurriculumApiController::class, 'modules']);
            Route::post('/modules', [CurriculumApiController::class, 'storeModule']);
            Route::delete('/modules/{module}', [CurriculumApiController::class, 'destroyModule']);
        });

        // 7. Presensi & Smart Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('/today', [AttendanceApiController::class, 'today']);
            Route::get('/wa-logs', [AttendanceApiController::class, 'waLogs']);
            Route::get('/monthly', [AttendanceApiController::class, 'monthlyReport']);
            Route::post('/scan-qr', [AttendanceApiController::class, 'scanQr']);
            Route::post('/scan-student', [AttendanceApiController::class, 'scanStudentQr']);
            Route::post('/manual', [AttendanceApiController::class, 'storeManual']);
        });

        // 8. Jurnal KBM Mengajar Guru
        Route::prefix('journals')->group(function () {
            Route::get('/', [JournalApiController::class, 'index']);
            Route::post('/', [JournalApiController::class, 'store']);
            Route::get('/{journal}', [JournalApiController::class, 'show']);
            Route::delete('/{journal}', [JournalApiController::class, 'destroy']);
        });

        // 9. Gradebook & Asesmen
        Route::prefix('gradebook')->group(function () {
            Route::get('/', [GradebookApiController::class, 'index']);
            Route::post('/', [GradebookApiController::class, 'store']);
            Route::get('/{assessment}/scores', [GradebookApiController::class, 'scores']);
            Route::post('/{assessment}/scores', [GradebookApiController::class, 'storeScores']);
            Route::delete('/{assessment}', [GradebookApiController::class, 'destroy']);
        });

        // 10. CBT Online Ujian Mobile
        Route::prefix('cbt')->group(function () {
            Route::get('/exams', [CbtApiController::class, 'exams']);
            Route::get('/exams/{exam}/workspace', [CbtApiController::class, 'workspace']);
            Route::post('/exams/{exam}/save-answer', [CbtApiController::class, 'saveAnswer']);
            Route::post('/exams/{exam}/submit', [CbtApiController::class, 'submit']);
        });

        // 11. E-Rapor Digital & Leger
        Route::prefix('rapor')->group(function () {
            Route::get('/', [ReportCardApiController::class, 'index']);
            Route::post('/generate', [ReportCardApiController::class, 'generateClass']);
            Route::get('/leger', [ReportCardApiController::class, 'leger']);
            Route::get('/summary', [GradebookApiController::class, 'raporSummary']);
            Route::get('/{reportCard}', [ReportCardApiController::class, 'show']);
            Route::post('/{reportCard}/notes', [ReportCardApiController::class, 'updateNotes']);
        });

        // 12. Projek P5 (Penguatan Profil Pelajar Pancasila)
        Route::prefix('p5')->group(function () {
            Route::get('/', [P5ApiController::class, 'index']);
            Route::post('/', [P5ApiController::class, 'store']);
            Route::get('/{project}/scores', [P5ApiController::class, 'scores']);
            Route::post('/{project}/scores', [P5ApiController::class, 'storeScores']);
            Route::delete('/{project}', [P5ApiController::class, 'destroy']);
        });

        // 13. Student Portal (Khusus Akun Siswa)
        Route::prefix('student')->group(function () {
            Route::get('/nilai', [StudentPortalApiController::class, 'nilai']);
            Route::get('/kehadiran', [StudentPortalApiController::class, 'kehadiran']);
        });

        // 14. User Management & Hak Akses
        Route::prefix('users')->group(function () {
            Route::get('/', [UserManagementApiController::class, 'index']);
            Route::post('/', [UserManagementApiController::class, 'store']);
            Route::put('/{user}', [UserManagementApiController::class, 'update']);
            Route::put('/{user}/reset-password', [UserManagementApiController::class, 'resetPassword']);
            Route::delete('/{user}', [UserManagementApiController::class, 'destroy']);
        });
    });
});
