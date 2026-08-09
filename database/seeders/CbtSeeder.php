<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamClass;
use App\Models\LearningObjective;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentExam;
use App\Models\StudentExamAnswer;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CbtSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();

        $teacherBudi = Teacher::where('name', 'like', '%Budi%')->first();
        $subjectDasarRpl = Subject::where('code', 'DASAR-RPL')->first();
        $tp1 = LearningObjective::where('code', 'TP-RPL-01.1')->first();
        $classXRpl1 = SchoolClass::where('name', 'X RPL 1')->first();

        if ($teacherBudi && $subjectDasarRpl) {
            // 1. Bank Soal
            $bank = QuestionBank::create([
                'school_id' => $school->id,
                'teacher_id' => $teacherBudi->id,
                'subject_id' => $subjectDasarRpl->id,
                'learning_objective_id' => $tp1?->id,
                'title' => 'Bank Soal Algoritma & Pemrograman Terstruktur Fase E',
                'phase' => 'E',
                'description' => 'Kumpulan butir soal formatif dan sumatif Kurikulum Merdeka mencakup LOTS, MOTS, dan HOTS.',
            ]);

            // Soal 1: Pilihan Ganda (PG) - MOTS
            $q1 = Question::create([
                'question_bank_id' => $bank->id,
                'type' => 'pg',
                'cognitive_level' => 'mots',
                'difficulty' => 'medium',
                'question_text' => 'Simbol diagram alir (flowchart) yang berfungsi untuk menentukan pengambilan keputusan berdasarkan kondisi logika tertentu (Decision) adalah...',
                'code_snippet' => null,
                'score_weight' => 20.00,
                'order_number' => 1,
                'explanation' => 'Simbol belah ketupat (Diamond) digunakan untuk Decision / Percabangan kondisi (IF-ELSE).',
            ]);

            QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'A', 'option_text' => 'Persegi Panjang (Process)', 'is_correct' => false]);
            QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'B', 'option_text' => 'Belah Ketupat (Diamond / Decision)', 'is_correct' => true]);
            QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'C', 'option_text' => 'Jajar Genjang (Input/Output)', 'is_correct' => false]);
            QuestionOption::create(['question_id' => $q1->id, 'option_label' => 'D', 'option_text' => 'Oval / Terminator (Start/End)', 'is_correct' => false]);

            // Soal 2: Pilihan Ganda Kompleks (PGK) - HOTS
            $q2 = Question::create([
                'question_bank_id' => $bank->id,
                'type' => 'pgk',
                'cognitive_level' => 'hots',
                'difficulty' => 'hard',
                'question_text' => 'Perhatikan potongan kode algoritma berikut. Manakah pernyataan yang BENAR mengenai variabel dan kondisi di bawah ini?',
                'code_snippet' => "int total_belanja = 150000;\nboolean is_member = true;\nif (total_belanja > 100000 && is_member) {\n    diskon = 0.1 * total_belanja;\n}",
                'score_weight' => 25.00,
                'order_number' => 2,
                'explanation' => 'Kondisi bernilai TRUE karena total belanja > 100000 DAN status member aktif, sehingga diskon bernilai 15.000.',
            ]);

            QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'A', 'option_text' => 'Program akan mengeksekusi blok kode diskon karena kedua kondisi logika AND bernilai TRUE.', 'is_correct' => true]);
            QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'B', 'option_text' => 'Variabel total_belanja bertipe data Integer (bilangan bulat).', 'is_correct' => true]);
            QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'C', 'option_text' => 'Diskon tidak akan dieksekusi jika is_member bernilai false.', 'is_correct' => true]);
            QuestionOption::create(['question_id' => $q2->id, 'option_label' => 'D', 'option_text' => 'Nilai diskon akhir yang didapatkan adalah sebesar 50.000.', 'is_correct' => false]);

            // Soal 3: Benar / Salah (True/False) - LOTS
            $q3 = Question::create([
                'question_bank_id' => $bank->id,
                'type' => 'true_false',
                'cognitive_level' => 'lots',
                'difficulty' => 'easy',
                'question_text' => 'Tipe data boolean hanya dapat menyimpan dua nilai kebenaran, yaitu TRUE atau FALSE.',
                'score_weight' => 15.00,
                'order_number' => 3,
                'explanation' => 'Tipe data boolean memang berukuran 1 bit dan hanya bernilai true atau false.',
            ]);

            QuestionOption::create(['question_id' => $q3->id, 'option_label' => 'A', 'option_text' => 'BENAR (True)', 'is_correct' => true]);
            QuestionOption::create(['question_id' => $q3->id, 'option_label' => 'B', 'option_text' => 'SALAH (False)', 'is_correct' => false]);

            // Soal 4: Menjodohkan (Matching) - MOTS
            $q4 = Question::create([
                'question_bank_id' => $bank->id,
                'type' => 'matching',
                'cognitive_level' => 'mots',
                'difficulty' => 'medium',
                'question_text' => 'Jodohkan tipe data di kolom sebelah kiri dengan contoh nilainya yang tepat di kolom sebelah kanan:',
                'score_weight' => 20.00,
                'order_number' => 4,
            ]);

            QuestionOption::create(['question_id' => $q4->id, 'option_text' => 'Integer (int)', 'matching_pair' => '42 (Bilangan Bulat)']);
            QuestionOption::create(['question_id' => $q4->id, 'option_text' => 'Float / Double', 'matching_pair' => '3.14 (Bilangan Desimal)']);
            QuestionOption::create(['question_id' => $q4->id, 'option_text' => 'String', 'matching_pair' => '"SMK Bisa Hebat" (Teks)']);

            // Soal 5: Essay / HOTS Analisis Kasus
            $q5 = Question::create([
                'question_bank_id' => $bank->id,
                'type' => 'essay',
                'cognitive_level' => 'hots',
                'difficulty' => 'hard',
                'question_text' => 'Jelaskan perbedaan mendasar antara struktur perulangan FOR Loop dengan WHILE Loop! Berikan contoh kasus nyata kapan seorang programmer SMK lebih tepat menggunakan WHILE daripada FOR!',
                'score_weight' => 20.00,
                'order_number' => 5,
                'explanation' => 'FOR digunakan saat jumlah iterasi sudah diketahui pasti. WHILE digunakan saat jumlah iterasi bergantung pada kondisi dinamis (misal menunggu input user).',
            ]);

            // 2. Exam Setup
            $exam = Exam::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'semester_id' => $semester?->id,
                'question_bank_id' => $bank->id,
                'teacher_id' => $teacherBudi->id,
                'subject_id' => $subjectDasarRpl->id,
                'title' => 'Sumatif Tengah Semester (CBT): Dasar Pemrograman & Algoritma',
                'token' => 'MERDEKA',
                'start_time' => now()->subHours(2),
                'end_time' => now()->addDays(7),
                'duration_minutes' => 60,
                'kktp_score' => 75.00,
                'randomize_questions' => true,
                'randomize_options' => true,
                'max_tab_switches' => 3,
                'status' => 'ongoing',
                'instructions' => "1. Kerjakan soal dengan jujur dan mandiri di Lab Komputer.\n2. Layar akan terkunci dalam mode Fullscreen. Dilarang berpindah tab browser (Maksimal toleransi 3x pelanggaran).\n3. Nilai akan otomatis dihitung saat Anda menekan tombol Selesai Ujian.",
            ]);

            if ($classXRpl1) {
                ExamClass::create(['exam_id' => $exam->id, 'class_id' => $classXRpl1->id]);

                // 3. Demo Student Exam Attempts
                $students = Student::where('current_class_id', $classXRpl1->id)->take(3)->get();

                foreach ($students as $idx => $st) {
                    $score = ($idx === 0) ? 95.00 : (($idx === 1) ? 80.00 : 70.00);

                    $studentExam = StudentExam::create([
                        'exam_id' => $exam->id,
                        'student_id' => $st->id,
                        'start_time' => now()->subMinutes(50),
                        'submit_time' => now()->subMinutes(10),
                        'duration_used_seconds' => 2400,
                        'status' => 'submitted',
                        'tab_switch_count' => ($idx === 2) ? 2 : 0,
                        'total_score' => $score,
                        'is_passed' => $score >= 75.00,
                        'ip_address' => '192.168.1.' . (100 + $idx),
                    ]);

                    // Answers
                    StudentExamAnswer::create([
                        'student_exam_id' => $studentExam->id,
                        'question_id' => $q1->id,
                        'answer_json' => ['selected_option' => 'B'],
                        'is_correct' => true,
                        'score_obtained' => 20.00,
                    ]);

                    StudentExamAnswer::create([
                        'student_exam_id' => $studentExam->id,
                        'question_id' => $q3->id,
                        'answer_json' => ['selected_option' => 'A'],
                        'is_correct' => true,
                        'score_obtained' => 15.00,
                    ]);
                }
            }
        }
    }
}
