<?php

use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CbtApiController;
use App\Http\Controllers\Api\V1\CurriculumApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\GradebookApiController;
use App\Http\Controllers\Api\V1\JournalApiController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\ScheduleApiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Android API Routes (V1) - MHC Smart School
|--------------------------------------------------------------------------
|
| Base URL: /api/v1
| 100% Feature Parity with Web Application (Auth, Profile, Dashboard,
| Master Data, Schedule, Curriculum, Attendance, KBM Journals, CBT, Gradebook).
|
*/

Route::prefix('v1')->group(function () {

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

        // 3. Master Data (Kelas, Siswa, Guru, Mapel, Jurusan)
        Route::prefix('master')->group(function () {
            Route::get('/classes', [MasterDataApiController::class, 'classes']);
            Route::get('/students', [MasterDataApiController::class, 'students']);
            Route::get('/teachers', [MasterDataApiController::class, 'teachers']);
            Route::get('/subjects', [MasterDataApiController::class, 'subjects']);
            Route::get('/majors', [MasterDataApiController::class, 'majors']);
        });

        // 4. Jadwal Pelajaran (Schedule)
        Route::prefix('schedule')->group(function () {
            Route::get('/my-schedule', [ScheduleApiController::class, 'mySchedule']);
            Route::get('/matrix', [ScheduleApiController::class, 'matrix']);
        });

        // 5. Kurikulum Merdeka (CP/TP, Bahan Ajar, Modul Ajar)
        Route::prefix('curriculum')->group(function () {
            Route::get('/outcomes', [CurriculumApiController::class, 'learningOutcomes']);
            Route::get('/materials', [CurriculumApiController::class, 'materials']);
            Route::get('/modules', [CurriculumApiController::class, 'modules']);
        });

        // 6. Presensi & Smart Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('/today', [AttendanceApiController::class, 'today']);
            Route::post('/scan-qr', [AttendanceApiController::class, 'scanQr']);
        });

        // 7. Jurnal KBM Mengajar Guru
        Route::get('/journals', [JournalApiController::class, 'index']);
        Route::post('/journals', [JournalApiController::class, 'store']);

        // 8. CBT Online Ujian Mobile
        Route::prefix('cbt')->group(function () {
            Route::get('/exams', [CbtApiController::class, 'exams']);
            Route::get('/exams/{exam}/workspace', [CbtApiController::class, 'workspace']);
            Route::post('/exams/{exam}/save-answer', [CbtApiController::class, 'saveAnswer']);
            Route::post('/exams/{exam}/submit', [CbtApiController::class, 'submit']);
        });

        // 9. Nilai & Rapor Digital
        Route::get('/gradebook', [GradebookApiController::class, 'index']);
        Route::get('/rapor/summary', [GradebookApiController::class, 'raporSummary']);
    });
});
