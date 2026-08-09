<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AtpBuilderController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CbtAnalyticsController;
use App\Http\Controllers\CbtExamController;
use App\Http\Controllers\CbtQuestionBankController;
use App\Http\Controllers\CbtStudentPortalController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\P5ProjectController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SchedulerController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeachingJournalController;
use App\Http\Controllers\TeachingModuleController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data Routes
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/school', [SchoolProfileController::class, 'index'])->name('school.index');
        Route::post('/school', [SchoolProfileController::class, 'update'])->name('school.update');
        Route::post('/school/wa-settings', [SchoolProfileController::class, 'updateWaSettings'])->name('school.wa-settings');
        Route::post('/school/wa-template', [SchoolProfileController::class, 'updateWaTemplate'])->name('school.wa-template');
        Route::post('/school/attendance-times', [SchoolProfileController::class, 'updateAttendanceTimes'])->name('school.attendance-times');
        Route::post('/school/wa-test', [SchoolProfileController::class, 'sendTestWa'])->name('school.wa-test');

        Route::get('/academic-year', [AcademicYearController::class, 'index'])->name('academic-year.index');
        Route::post('/academic-year', [AcademicYearController::class, 'store'])->name('academic-year.store');
        Route::post('/academic-year/{academicYear}/set-active', [AcademicYearController::class, 'setActive'])->name('academic-year.set-active');
        Route::post('/semester/{semester}/set-active', [AcademicYearController::class, 'setActiveSemester'])->name('semester.set-active');

        Route::get('/majors', [MajorController::class, 'index'])->name('majors.index');
        Route::post('/majors', [MajorController::class, 'store'])->name('majors.store');
        Route::put('/majors/{major}', [MajorController::class, 'update'])->name('majors.update');
        Route::delete('/majors/{major}', [MajorController::class, 'destroy'])->name('majors.destroy');

        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/template', [TeacherController::class, 'downloadTemplate'])->name('teachers.template');
        Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

        Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/template', [SchoolClassController::class, 'downloadTemplate'])->name('classes.template');
        Route::post('/classes/import', [SchoolClassController::class, 'import'])->name('classes.import');
        Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
        Route::put('/classes/{class}', [SchoolClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{class}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('/students/id-cards', [StudentController::class, 'printIdCards'])->name('students.id-cards');
        Route::get('/students/{student}/id-card', [StudentController::class, 'printIdCard'])->name('students.id-card');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    });

    // Kurikulum Merdeka Routes
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.template');
        Route::post('/subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        Route::get('/cp-tp', [CurriculumController::class, 'index'])->name('cp-tp.index');
        Route::post('/cp', [CurriculumController::class, 'storeCp'])->name('cp.store');
        Route::put('/cp/{learningOutcome}', [CurriculumController::class, 'updateCp'])->name('cp.update');
        Route::delete('/cp/{learningOutcome}', [CurriculumController::class, 'destroyCp'])->name('cp.destroy');

        Route::post('/tp', [CurriculumController::class, 'storeTp'])->name('tp.store');
        Route::put('/tp/{learningObjective}', [CurriculumController::class, 'updateTp'])->name('tp.update');
        Route::delete('/tp/{learningObjective}', [CurriculumController::class, 'destroyTp'])->name('tp.destroy');

        Route::get('/atp', [AtpBuilderController::class, 'index'])->name('atp.index');
        Route::post('/atp/header', [AtpBuilderController::class, 'storeHeader'])->name('atp.header.store');
        Route::put('/atp/header/{learningPath}', [AtpBuilderController::class, 'updateHeader'])->name('atp.header.update');
        Route::delete('/atp/header/{learningPath}', [AtpBuilderController::class, 'destroyHeader'])->name('atp.header.destroy');
        Route::post('/atp/item', [AtpBuilderController::class, 'addItem'])->name('atp.item.store');
        Route::put('/atp/item/{item}', [AtpBuilderController::class, 'updateItem'])->name('atp.item.update');
        Route::delete('/atp/item/{item}', [AtpBuilderController::class, 'deleteItem'])->name('atp.item.destroy');

        Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

        Route::get('/modules', [TeachingModuleController::class, 'index'])->name('modules.index');
        Route::get('/modules/create', [TeachingModuleController::class, 'create'])->name('modules.create');
        Route::post('/modules', [TeachingModuleController::class, 'store'])->name('modules.store');
        Route::get('/modules/{module}', [TeachingModuleController::class, 'show'])->name('modules.show');
        Route::get('/modules/{module}/edit', [TeachingModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/modules/{module}', [TeachingModuleController::class, 'update'])->name('modules.update');
        Route::get('/modules/{module}/print', [TeachingModuleController::class, 'print'])->name('modules.print');
        Route::delete('/modules/{module}', [TeachingModuleController::class, 'destroy'])->name('modules.destroy');
    });

    // Automatic Scheduler Routes
    Route::prefix('scheduler')->name('scheduler.')->group(function () {
        Route::get('/', [SchedulerController::class, 'index'])->name('index');
        Route::get('/loads', [SchedulerController::class, 'teachingLoads'])->name('loads');
        Route::post('/loads', [SchedulerController::class, 'storeTeachingLoad'])->name('loads.store');
        Route::delete('/loads/{load}', [SchedulerController::class, 'destroyTeachingLoad'])->name('loads.destroy');

        Route::get('/generator', [SchedulerController::class, 'generator'])->name('generator');
        Route::post('/generate', [SchedulerController::class, 'runGenerator'])->name('generate');

        Route::get('/conflicts', [SchedulerController::class, 'conflicts'])->name('conflicts');
    });

    // Presensi Smart Routes
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/wa-logs', [AttendanceController::class, 'waLogs'])->name('wa-logs');
        Route::get('/monthly-report', [AttendanceController::class, 'printMonthlyReport'])->name('monthly-report');
        Route::get('/qr', [AttendanceController::class, 'qrScanner'])->name('qr');
        Route::post('/scan', [AttendanceController::class, 'recordQrScan'])->name('scan');
        Route::post('/scan-student', [AttendanceController::class, 'scanStudentQr'])->name('scan-student');
        Route::post('/manual', [AttendanceController::class, 'storeManual'])->name('manual');
    });

    // Jurnal Mengajar Digital Routes
    Route::prefix('journals')->name('journals.')->group(function () {
        Route::get('/', [TeachingJournalController::class, 'index'])->name('index');
        Route::get('/create', [TeachingJournalController::class, 'create'])->name('create');
        Route::post('/', [TeachingJournalController::class, 'store'])->name('store');
        Route::get('/{journal}', [TeachingJournalController::class, 'show'])->name('show');
        Route::delete('/{journal}', [TeachingJournalController::class, 'destroy'])->name('destroy');
    });

    // Gradebook & Asesmen Routes
    Route::prefix('gradebook')->name('gradebook.')->group(function () {
        Route::get('/', [GradebookController::class, 'index'])->name('index');
        Route::get('/create', [GradebookController::class, 'create'])->name('create');
        Route::post('/', [GradebookController::class, 'store'])->name('store');
        Route::get('/{assessment}/scores', [GradebookController::class, 'scores'])->name('scores');
        Route::post('/{assessment}/scores', [GradebookController::class, 'storeScores'])->name('scores.store');
        Route::delete('/{assessment}', [GradebookController::class, 'destroy'])->name('destroy');
    });

    // Computer Based Test (CBT) Routes
    Route::prefix('cbt')->name('cbt.')->group(function () {
        Route::get('/banks', [CbtQuestionBankController::class, 'index'])->name('banks.index');
        Route::post('/banks', [CbtQuestionBankController::class, 'store'])->name('banks.store');
        Route::get('/banks/{bank}', [CbtQuestionBankController::class, 'show'])->name('banks.show');
        Route::post('/banks/{bank}/questions', [CbtQuestionBankController::class, 'storeQuestion'])->name('banks.questions.store');
        Route::put('/questions/{question}', [CbtQuestionBankController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{question}', [CbtQuestionBankController::class, 'destroyQuestion'])->name('questions.destroy');
        Route::delete('/banks/{bank}', [CbtQuestionBankController::class, 'destroy'])->name('banks.destroy');

        Route::get('/exams', [CbtExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [CbtExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [CbtExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}/monitor', [CbtExamController::class, 'monitor'])->name('exams.monitor');
        Route::post('/exams/{exam}/refresh-token', [CbtExamController::class, 'refreshToken'])->name('exams.refresh-token');
        Route::delete('/exams/{exam}', [CbtExamController::class, 'destroy'])->name('exams.destroy');

        Route::get('/portal', [CbtStudentPortalController::class, 'index'])->name('portal.index');
        Route::post('/portal/start/{exam}', [CbtStudentPortalController::class, 'startExam'])->name('portal.start');
        Route::get('/portal/workspace/{exam}', [CbtStudentPortalController::class, 'workspace'])->name('portal.workspace');
        Route::post('/portal/save-answer/{exam}', [CbtStudentPortalController::class, 'saveAnswer'])->name('portal.save-answer');
        Route::post('/portal/tab-switch/{exam}', [CbtStudentPortalController::class, 'recordTabSwitch'])->name('portal.tab-switch');
        Route::post('/portal/submit/{exam}', [CbtStudentPortalController::class, 'submitExam'])->name('portal.submit');

        Route::get('/exams/{exam}/analytics', [CbtAnalyticsController::class, 'index'])->name('exams.analytics');
        Route::get('/analytics/{studentExam}/detail', [CbtAnalyticsController::class, 'studentDetail'])->name('analytics.student-detail');
        Route::post('/analytics/grade-essay/{answer}', [CbtAnalyticsController::class, 'gradeEssay'])->name('analytics.grade-essay');
    });

    // E-Rapor Kurikulum Merdeka & Leger Nilai Routes
    Route::prefix('rapor')->name('rapor.')->group(function () {
        Route::get('/', [ReportCardController::class, 'index'])->name('index');
        Route::post('/generate', [ReportCardController::class, 'generateClass'])->name('generate');
        Route::get('/leger', [ReportCardController::class, 'leger'])->name('leger');
        Route::get('/{reportCard}', [ReportCardController::class, 'show'])->name('show');
        Route::post('/{reportCard}/notes', [ReportCardController::class, 'updateNotes'])->name('notes.update');
        Route::get('/{reportCard}/print', [ReportCardController::class, 'printAkademik'])->name('print');
    });

    // Projek Penguatan Profil Pelajar Pancasila (P5) Routes
    Route::prefix('p5')->name('p5.')->group(function () {
        Route::get('/', [P5ProjectController::class, 'index'])->name('index');
        Route::get('/create', [P5ProjectController::class, 'create'])->name('create');
        Route::post('/', [P5ProjectController::class, 'store'])->name('store');
        Route::get('/{project}/scores', [P5ProjectController::class, 'scores'])->name('scores');
        Route::post('/{project}/scores', [P5ProjectController::class, 'storeScores'])->name('scores.store');
        Route::get('/{project}/print/{student}', [P5ProjectController::class, 'printP5'])->name('print');
        Route::delete('/{project}', [P5ProjectController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Pengguna & Hak Akses
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::put('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    // Portal Siswa: Nilai & Kehadiran
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/nilai', [StudentPortalController::class, 'nilai'])->name('nilai');
        Route::get('/kehadiran', [StudentPortalController::class, 'kehadiran'])->name('kehadiran');
    });
});
