<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\P5Project;
use App\Models\P5ProjectDimension;
use App\Models\P5StudentScore;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Services\ReportCardGeneratorService;
use Illuminate\Database\Seeder;

class ReportCardSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();
        $classXRpl1 = SchoolClass::where('name', 'X RPL 1')->first();

        // 1. Generate E-Rapor Akademik untuk Rombel X RPL 1
        if ($classXRpl1) {
            $generator = new ReportCardGeneratorService();
            $generator->generateForClass($classXRpl1->id, $ay->id, $semester?->id);

            // 2. Buat Projek Penguatan Profil Pelajar Pancasila (P5)
            $p5 = P5Project::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'semester_id' => $semester?->id,
                'class_id' => $classXRpl1->id,
                'theme' => 'Kebekerjaan',
                'title' => 'Membangun Portofolio Digital Software Developer Profesional',
                'description' => 'Peserta didik merancang, mengembangkan, dan mempublikasikan portofolio karya aplikasi berbasis web serta melakukan simulasi wawancara industri kerja teknologi informasi.',
            ]);

            $dim1 = P5ProjectDimension::create([
                'p5_project_id' => $p5->id,
                'dimension_name' => 'Mandiri',
                'element' => 'Pemahaman diri dan situasi yang dihadapi',
                'sub_element' => 'Mengenali kualitas dan minat diri serta tantangan yang dihadapi di dunia kerja RPL',
                'target_phase' => 'E',
            ]);

            $dim2 = P5ProjectDimension::create([
                'p5_project_id' => $p5->id,
                'dimension_name' => 'Bernalar Kritis',
                'element' => 'Refleksi pemikiran dan proses berpikir',
                'sub_element' => 'Mengidentifikasi, mengklarifikasi, dan mengolah informasi solusi komputasi',
                'target_phase' => 'E',
            ]);

            $dim3 = P5ProjectDimension::create([
                'p5_project_id' => $p5->id,
                'dimension_name' => 'Kreatif',
                'element' => 'Menghasilkan gagasan yang orisinal',
                'sub_element' => 'Mengeksplorasi dan mengekspresikan ide kreatif dalam bentuk karya aplikasi web',
                'target_phase' => 'E',
            ]);

            // Nilai P5 Siswa
            $students = Student::where('current_class_id', $classXRpl1->id)->get();
            $dimensions = [$dim1, $dim2, $dim3];

            foreach ($dimensions as $dim) {
                foreach ($students as $idx => $st) {
                    $score = ($idx % 3 === 0) ? 'SAB' : 'BSH';
                    P5StudentScore::create([
                        'p5_project_dimension_id' => $dim->id,
                        'student_id' => $st->id,
                        'score' => $score,
                        'teacher_notes' => 'Peserta didik aktif berkolaborasi dan menunjukkan inisiatif tinggi dalam perancangan produk portofolio.',
                    ]);
                }
            }
        }
    }
}
