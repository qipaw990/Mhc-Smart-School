<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Attendance;
use App\Models\QrAttendanceSession;
use App\Models\ScheduleItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\User;
use Tests\TestCase;

class PhaseFourAttendanceAndGradebookTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->first();
    }

    public function test_attendance_index_and_qr_pages(): void
    {
        $this->actingAs($this->admin)
            ->get('/attendance')
            ->assertStatus(200)
            ->assertSee('Presensi Siswa (Multi-Method Smart Attendance)', false);

        $this->actingAs($this->admin)
            ->get('/attendance/qr')
            ->assertStatus(200)
            ->assertSee('Dynamic QR Code Presensi (Anti-Cheat)', false);

        $this->actingAs($this->admin)
            ->get('/attendance/monthly-report')
            ->assertStatus(200)
            ->assertSee('REKAPITULASI PRESENSI SISWA BULANAN', false);
    }

    public function test_can_submit_manual_batch_attendance(): void
    {
        $class = SchoolClass::first();
        $student = Student::where('current_class_id', $class->id)->first();

        $response = $this->actingAs($this->admin)->post('/attendance/manual', [
            'class_id' => $class->id,
            'date' => now()->toDateString(),
            'statuses' => [
                $student->id => 'H',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'status' => 'H',
        ]);
    }

    public function test_teaching_journals_index_create_and_store(): void
    {
        $this->actingAs($this->admin)
            ->get('/journals')
            ->assertStatus(200)
            ->assertSee('Jurnal Mengajar Digital Guru', false);

        $this->actingAs($this->admin)
            ->get('/journals/create')
            ->assertStatus(200)
            ->assertSee('Catat Jurnal Mengajar Harian', false);

        $teacher = Teacher::first();
        $class = SchoolClass::first();
        $subject = Subject::first();

        $response = $this->actingAs($this->admin)->post('/journals', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'period_start' => 1,
            'period_end' => 3,
            'topic_activity' => 'Membahas studi kasus pemrograman web terstruktur di lab.',
            'notes_challenges' => 'KBM berjalan tertib dan lancar.',
            'student_present_count' => 32,
            'student_absent_count' => 0,
        ]);

        $response->assertRedirect('/journals');
        $this->assertDatabaseHas('teaching_journals', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'student_present_count' => 32,
        ]);
    }

    public function test_gradebook_and_assessment_scores(): void
    {
        $this->actingAs($this->admin)
            ->get('/gradebook')
            ->assertStatus(200)
            ->assertSee('Gradebook & Asesmen Kurikulum Merdeka', false);

        $this->actingAs($this->admin)
            ->get('/gradebook/create')
            ->assertStatus(200)
            ->assertSee('Buat Asesmen Kurikulum Merdeka', false);

        $teacher = Teacher::first();
        $class = SchoolClass::first();
        $subject = Subject::first();

        // Clear any previous test assessment
        Assessment::where('title', 'Formatif Test Suite: Algoritma Dasar')->delete();

        // Create Assessment
        $response = $this->actingAs($this->admin)->post('/gradebook', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Formatif Test Suite: Algoritma Dasar',
            'type' => 'formative',
            'kktp_score' => 75.00,
            'max_score' => 100.00,
            'date' => now()->toDateString(),
            'description' => 'Tugas asesmen formatif otomatis.',
        ]);

        $assessment = Assessment::where('title', 'Formatif Test Suite: Algoritma Dasar')->latest('id')->first();
        $this->assertNotNull($assessment);
        $response->assertRedirect('/gradebook/' . $assessment->id . '/scores');

        // Input Scores
        $student = Student::where('current_class_id', $class->id)->first();
        $scoreResponse = $this->actingAs($this->admin)->post('/gradebook/' . $assessment->id . '/scores', [
            'scores' => [
                $student->id => [
                    'score' => 88.50,
                    'is_remedial' => 0,
                    'teacher_notes' => 'Lulus di atas KKTP',
                ],
            ],
        ]);

        $scoreResponse->assertRedirect();
        $this->assertDatabaseHas('assessment_scores', [
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'final_score' => 88.50,
            'achievement_status' => 'achieved',
        ]);
    }
}
