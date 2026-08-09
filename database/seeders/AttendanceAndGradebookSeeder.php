<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Attendance;
use App\Models\LearningObjective;
use App\Models\ScheduleItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Illuminate\Database\Seeder;

class AttendanceAndGradebookSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();

        $classXRpl1 = SchoolClass::where('name', 'X RPL 1')->first();
        $teacherBudi = Teacher::where('name', 'like', '%Budi%')->first();
        $subjectDasarRpl = Subject::where('code', 'DASAR-RPL')->first();
        $tp1 = LearningObjective::where('code', 'TP-RPL-01.1')->first();
        $tp2 = LearningObjective::where('code', 'TP-RPL-01.2')->first();
        $scheduleItem = ScheduleItem::where('class_id', $classXRpl1?->id)->first();

        $students = Student::where('current_class_id', $classXRpl1?->id)->get();

        // 1. Presensi Siswa X RPL 1
        if ($students->isNotEmpty()) {
            foreach ($students as $idx => $student) {
                $status = ($idx === 4) ? 'S' : (($idx === 5) ? 'I' : 'H');

                Attendance::create([
                    'school_id' => $school->id,
                    'academic_year_id' => $ay->id,
                    'schedule_item_id' => $scheduleItem?->id,
                    'student_id' => $student->id,
                    'teacher_id' => $teacherBudi?->id,
                    'date' => now()->toDateString(),
                    'time' => now()->toTimeString(),
                    'type' => 'subject_session',
                    'method' => 'qr_dynamic',
                    'status' => $status,
                    'latitude' => -6.595038,
                    'longitude' => 106.816635,
                    'device_info' => 'Android / MHC Smart Student App',
                    'notes' => $status === 'S' ? 'Sakit flu dengan surat dokter' : null,
                ]);
            }
        }

        // 2. Jurnal Mengajar Guru Budi
        if ($teacherBudi && $subjectDasarRpl && $classXRpl1) {
            TeachingJournal::create([
                'school_id' => $school->id,
                'schedule_item_id' => $scheduleItem?->id,
                'teacher_id' => $teacherBudi->id,
                'class_id' => $classXRpl1->id,
                'subject_id' => $subjectDasarRpl->id,
                'learning_objective_id' => $tp1?->id,
                'date' => now()->toDateString(),
                'period_start' => 1,
                'period_end' => 3,
                'topic_activity' => "Membahas konsep logika dasar pemrograman, notasi algoritma flowchart, dan studi kasus sistem kasir minimarket.",
                'notes_challenges' => "Siswa sangat antusias dalam diskusi kelompok membuat flowchart di Lab Komputer. 2 siswa izin/sakit.",
                'student_present_count' => max(0, $students->count() - 2),
                'student_absent_count' => min(2, $students->count()),
                'status' => 'submitted',
            ]);
        }

        // 3. Asesmen & Gradebook Kurikulum Merdeka
        if ($teacherBudi && $subjectDasarRpl && $classXRpl1) {
            // Asesmen Formatif 1
            $formative1 = Assessment::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'semester_id' => $semester?->id,
                'teacher_id' => $teacherBudi->id,
                'subject_id' => $subjectDasarRpl->id,
                'class_id' => $classXRpl1->id,
                'learning_objective_id' => $tp1?->id,
                'title' => 'Formatif 1: Praktik Flowchart Algoritma',
                'type' => 'formative',
                'kktp_score' => 75.00,
                'max_score' => 100.00,
                'date' => now()->subDays(5)->toDateString(),
                'description' => 'Tugas mandiri penyusunan diagram alir flowchart studi kasus kasir.',
            ]);

            // Asesmen Formatif 2
            $formative2 = Assessment::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'semester_id' => $semester?->id,
                'teacher_id' => $teacherBudi->id,
                'subject_id' => $subjectDasarRpl->id,
                'class_id' => $classXRpl1->id,
                'learning_objective_id' => $tp2?->id,
                'title' => 'Formatif 2: Sintaks Percabangan IF-ELSE',
                'type' => 'formative',
                'kktp_score' => 75.00,
                'max_score' => 100.00,
                'date' => now()->subDays(2)->toDateString(),
                'description' => 'Kuis lab coding struktur percabangan.',
            ]);

            // Asesmen Sumatif Lingkup Materi
            $summativeTp = Assessment::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'semester_id' => $semester?->id,
                'teacher_id' => $teacherBudi->id,
                'subject_id' => $subjectDasarRpl->id,
                'class_id' => $classXRpl1->id,
                'learning_objective_id' => $tp1?->id,
                'title' => 'Sumatif Lingkup Materi: Algoritma & Struktur Data Dasar',
                'type' => 'summative_tp',
                'kktp_score' => 75.00,
                'max_score' => 100.00,
                'date' => now()->toDateString(),
                'description' => 'Tes praktik dan teori tengah modul.',
            ]);

            $assessments = [$formative1, $formative2, $summativeTp];

            foreach ($assessments as $asm) {
                foreach ($students as $idx => $student) {
                    $baseScore = 80 + (($idx % 4) * 5) - (($idx % 3) * 3);
                    $score = min(100, max(65, $baseScore));
                    $isRemedial = $score < 75;
                    $finalScore = $isRemedial ? 78 : $score;

                    AssessmentScore::create([
                        'assessment_id' => $asm->id,
                        'student_id' => $student->id,
                        'score' => $score,
                        'is_remedial' => $isRemedial,
                        'remedial_score' => $isRemedial ? 78 : null,
                        'final_score' => $finalScore,
                        'achievement_status' => $finalScore >= 75 ? 'achieved' : 'not_achieved',
                        'teacher_notes' => $finalScore >= 85 ? 'Sangat menguasai konsep dan implementasi lab.' : 'Mencapai kriteria ketuntasan.',
                    ]);
                }
            }
        }
    }
}
