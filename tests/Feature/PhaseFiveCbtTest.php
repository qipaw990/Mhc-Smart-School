<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentExam;
use App\Models\StudentExamAnswer;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class PhaseFiveCbtTest extends TestCase
{
    protected $admin;
    protected $studentUser;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->first();
        $this->studentUser = User::where('username', 'like', '006%')->first();
        $this->student = Student::where('user_id', $this->studentUser->id)->first();
    }

    public function test_question_banks_index_and_show(): void
    {
        $this->actingAs($this->admin)
            ->get('/cbt/banks')
            ->assertStatus(200)
            ->assertSee('Bank Soal Digital Multi-Tipe', false);

        $bank = QuestionBank::first();
        if ($bank) {
            $this->actingAs($this->admin)
                ->get('/cbt/banks/' . $bank->id)
                ->assertStatus(200)
                ->assertSee($bank->title);
        }
    }

    public function test_can_add_question_to_bank(): void
    {
        $bank = QuestionBank::first();

        $response = $this->actingAs($this->admin)->post('/cbt/banks/' . $bank->id . '/questions', [
            'type' => 'pg',
            'cognitive_level' => 'mots',
            'difficulty' => 'medium',
            'question_text' => 'Berapa hasil dari 2 pangkat 3 dalam operasi pemrograman?',
            'score_weight' => 20,
            'correct_option' => 'C',
            'options' => [
                'A' => '4',
                'B' => '6',
                'C' => '8',
                'D' => '16',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', [
            'question_bank_id' => $bank->id,
            'question_text' => 'Berapa hasil dari 2 pangkat 3 dalam operasi pemrograman?',
        ]);
    }

    public function test_can_edit_question_in_bank(): void
    {
        $question = Question::first();
        if ($question) {
            $response = $this->actingAs($this->admin)->put('/cbt/questions/' . $question->id, [
                'type' => 'pg',
                'cognitive_level' => 'hots',
                'difficulty' => 'hard',
                'question_text' => 'Pertanyaan yang telah diperbarui melalui fitur edit soal',
                'score_weight' => 25,
                'correct_option' => 'B',
                'options' => [
                    'A' => 'Jawaban A Baru',
                    'B' => 'Jawaban B Baru (Benar)',
                    'C' => 'Jawaban C Baru',
                    'D' => 'Jawaban D Baru',
                ],
                'explanation' => 'Penjelasan baru setelah diedit',
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('questions', [
                'id' => $question->id,
                'cognitive_level' => 'hots',
                'difficulty' => 'hard',
                'question_text' => 'Pertanyaan yang telah diperbarui melalui fitur edit soal',
                'score_weight' => 25,
            ]);
            $this->assertDatabaseHas('question_options', [
                'question_id' => $question->id,
                'option_label' => 'B',
                'option_text' => 'Jawaban B Baru (Benar)',
                'is_correct' => 1,
            ]);
        }
    }

    public function test_cbt_exams_management_and_proctor_monitor(): void
    {
        $this->actingAs($this->admin)
            ->get('/cbt/exams')
            ->assertStatus(200)
            ->assertSee('Jadwal & Proktor Ujian CBT', false);

        $this->actingAs($this->admin)
            ->get('/cbt/exams/create')
            ->assertStatus(200)
            ->assertSee('Jadwalkan Sesi Ujian CBT Baru', false);

        $exam = Exam::first();
        if ($exam) {
            $this->actingAs($this->admin)
                ->get('/cbt/exams/' . $exam->id . '/monitor')
                ->assertStatus(200)
                ->assertSee('Live Monitoring Proktor CBT');
        }
    }

    public function test_student_cbt_portal_workspace_and_auto_save(): void
    {
        $exam = Exam::first();
        if (!$exam) {
            $bank = QuestionBank::first();
            $teacher = Teacher::first();
            $subject = Subject::first();
            $ay = AcademicYear::where('is_active', true)->first();
            $exam = Exam::create([
                'school_id' => 1,
                'academic_year_id' => $ay?->id ?? 1,
                'question_bank_id' => $bank?->id ?? 1,
                'teacher_id' => $teacher?->id ?? 1,
                'subject_id' => $subject?->id ?? 1,
                'title' => 'Ujian Test CBT',
                'token' => 'TOKEN1',
                'start_time' => now()->subHour(),
                'end_time' => now()->addHours(5),
                'duration_minutes' => 60,
                'kktp_score' => 75,
                'status' => 'published',
            ]);
        }

        // Clear any previous attempts for this test student
        StudentExam::where('exam_id', $exam->id)->where('student_id', $this->student->id)->delete();

        // Student Portal
        $this->actingAs($this->studentUser)
            ->get('/cbt/portal')
            ->assertStatus(200)
            ->assertSee('Portal Ujian CBT Siswa', false);

        // Start Exam with Token
        $startResponse = $this->actingAs($this->studentUser)->post('/cbt/portal/start/' . $exam->id, [
            'token' => $exam->token,
        ]);
        $startResponse->assertRedirect('/cbt/portal/workspace/' . $exam->id);

        // Workspace
        $this->actingAs($this->studentUser)
            ->get('/cbt/portal/workspace/' . $exam->id)
            ->assertStatus(200)
            ->assertSee('Sisa Waktu');

        // Auto Save Answer API
        $question = $exam->questionBank?->questions?->first() ?? Question::first();
        $saveResponse = $this->actingAs($this->studentUser)->postJson('/cbt/portal/save-answer/' . $exam->id, [
            'question_id' => $question->id,
            'answer' => 'B',
            'is_doubtful' => false,
        ]);
        $saveResponse->assertStatus(200)->assertJson(['status' => 'success']);

        // Tab Switch Anti-Cheat API
        $tabResponse = $this->actingAs($this->studentUser)->postJson('/cbt/portal/tab-switch/' . $exam->id);
        $tabResponse->assertStatus(200)->assertJson(['status' => 'success']);

        // Submit Exam
        $submitResponse = $this->actingAs($this->studentUser)->post('/cbt/portal/submit/' . $exam->id);
        $submitResponse->assertRedirect('/cbt/portal');
    }

    public function test_cbt_analytics_and_item_difficulty(): void
    {
        $exam = Exam::first();
        if ($exam) {
            $this->actingAs($this->admin)
                ->get('/cbt/exams/' . $exam->id . '/analytics')
                ->assertStatus(200)
                ->assertSee('Hasil & Analisis Butir Soal', false);
        }
    }
}
