<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingModule;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $webDev = Subject::where('code', 'WEB-DEV')->first();
        $dasarRpl = Subject::where('code', 'DASAR-RPL')->first();
        $budiTeacher = Teacher::where('name', 'like', '%Budi%')->first();
        $classXRpl1 = SchoolClass::where('name', 'X RPL 1')->first();

        // 1. Capaian Pembelajaran (CP) untuk Dasar-Dasar RPL (Fase E)
        if ($dasarRpl) {
            $cp1 = LearningOutcome::create([
                'subject_id' => $dasarRpl->id,
                'academic_year_id' => $ay?->id,
                'phase' => 'E',
                'code' => 'CP-RPL-01',
                'element' => 'Pemrograman Terstruktur & Algoritma',
                'description' => 'Pada akhir fase E, peserta didik mampu menerapkan pemrograman terstruktur, memahami sintaksis dasar bahasa pemrograman, variabel, tipe data, operator, percabangan, perulangan, dan fungsi untuk memecahkan masalah komputasi.',
                'status' => 'active',
            ]);

            // TP di bawah CP 1
            $tp1 = LearningObjective::create([
                'learning_outcome_id' => $cp1->id,
                'code' => 'TP-RPL-01.1',
                'order_number' => 1,
                'description' => 'Memahami konsep logika algoritma, flowchart, dan pseudocode dalam penyelesaian masalah pemrograman.',
                'semester_number' => 1,
                'estimated_hours' => 8,
                'status' => 'active',
            ]);

            $tp2 = LearningObjective::create([
                'learning_outcome_id' => $cp1->id,
                'code' => 'TP-RPL-01.2',
                'order_number' => 2,
                'description' => 'Menerapkan tipe data, variabel, operator, dan struktur kontrol percabangan (if-else, switch) dalam bahasa pemrograman.',
                'semester_number' => 1,
                'estimated_hours' => 12,
                'status' => 'active',
            ]);

            $tp3 = LearningObjective::create([
                'learning_outcome_id' => $cp1->id,
                'code' => 'TP-RPL-01.3',
                'order_number' => 3,
                'description' => 'Menerapkan struktur perulangan (for, while, do-while) dan manipulasi array untuk mengolah sekumpulan data.',
                'semester_number' => 1,
                'estimated_hours' => 12,
                'status' => 'active',
            ]);

            // Materi untuk TP 1
            Material::create([
                'learning_objective_id' => $tp1->id,
                'title' => 'Pengenalan Algoritma & Flowchart Dasar',
                'description' => 'Materi dasar memahami simbol flowchart, urutan instruksi, dan konversi ke pseudocode.',
                'video_url' => 'https://www.youtube.com/watch?v=example',
                'external_link' => 'https://developer.mozilla.org',
                'estimated_minutes' => 90,
                'sequence_order' => 1,
            ]);

            Material::create([
                'learning_objective_id' => $tp2->id,
                'title' => 'Struktur Percabangan dan Kondisi Logika',
                'description' => 'Materi penerapan IF-ELSE bertingkat dan studi kasus logika kelulusan nilai.',
                'estimated_minutes' => 90,
                'sequence_order' => 2,
            ]);

            // 2. ATP Builder (Alur Tujuan Pembelajaran Timeline)
            $atp = LearningPath::create([
                'subject_id' => $dasarRpl->id,
                'academic_year_id' => $ay->id,
                'major_id' => $dasarRpl->major_id,
                'phase' => 'E',
                'semester_number' => 1,
                'title' => 'ATP Dasar-Dasar Kejuruan RPL Kelas X Semester Ganjil',
                'description' => 'Alur pembelajaran semester ganjil dimulai dari logika algoritma dasar menuju pembuatan aplikasi console terstruktur.',
            ]);

            LearningPathItem::create([
                'learning_path_id' => $atp->id,
                'learning_objective_id' => $tp1->id,
                'sequence_order' => 1,
                'week_number' => 1,
                'hour_allocation' => 6,
                'topic' => 'Konsep Dasar Algoritma & Notasi Flowchart',
                'assessment_plan' => 'Asesmen Diagnostik & Tugas Praktik Membuat Flowchart Kasus Kasir',
            ]);

            LearningPathItem::create([
                'learning_path_id' => $atp->id,
                'learning_objective_id' => $tp2->id,
                'sequence_order' => 2,
                'week_number' => 2,
                'hour_allocation' => 6,
                'topic' => 'Sintaks Dasar Variabel, Tipe Data & Operator',
                'assessment_plan' => 'Kuis Formatif Lab Komputer',
            ]);

            LearningPathItem::create([
                'learning_path_id' => $atp->id,
                'learning_objective_id' => $tp2->id,
                'sequence_order' => 3,
                'week_number' => 3,
                'hour_allocation' => 6,
                'topic' => 'Struktur Percabangan IF-ELSE & Switch Case',
                'assessment_plan' => 'Tugas Praktik Coding Program Seleksi Nilai Siswa',
            ]);

            LearningPathItem::create([
                'learning_path_id' => $atp->id,
                'learning_objective_id' => $tp3->id,
                'sequence_order' => 4,
                'week_number' => 4,
                'hour_allocation' => 6,
                'topic' => 'Perulangan For Loop & Array 1 Dimensi',
                'assessment_plan' => 'Sumatif Tengah Semester Praktik Lab',
            ]);

            // 3. Sample Modul Ajar Generator (Indonesian SMK Merdeka standard)
            if ($budiTeacher) {
                TeachingModule::create([
                    'subject_id' => $dasarRpl->id,
                    'teacher_id' => $budiTeacher->id,
                    'class_id' => $classXRpl1?->id,
                    'learning_outcome_id' => $cp1->id,
                    'learning_objective_id' => $tp1->id,
                    'title' => 'Modul Ajar: Algoritma dan Pemrograman Dasar Fase E',
                    'phase' => 'E',
                    'grade_level' => 'X',
                    'allocated_hours' => 6,
                    'learning_model' => 'Problem Based Learning (PBL)',
                    'methods' => 'Diskusi Kelompok, Demonstrasi Guru, Praktik Lab Komputer',
                    'target_students' => 'Siswa Reguler / Tipikal',
                    'preliminary_activities' => "1. Guru mengucap salam, berdoa, dan memeriksa kehadiran siswa via MHC Smart Attendance.\n2. Apersepsi: Mengaitkan algoritma dengan langkah pembuatan kopi / resep masakan di kehidupan sehari-hari.\n3. Guru menyampaikan Tujuan Pembelajaran (TP) dan rubrik asesmen.",
                    'core_activities' => "1. Orientasi Masalah: Guru menampilkan studi kasus sistem transaksi kasir minimarket.\n2. Mengorganisasi Siswa: Siswa dibagi menjadi kelompok beranggotakan 4 orang di Lab RPL.\n3. Membimbing Penyelidikan: Siswa menyusun diagram alir (Flowchart) dan menulis pseudocode solusi.\n4. Mengembangkan Hasil: Siswa menguji logika algoritma menggunakan compiler / simulator online.\n5. Evaluasi: Perwakilan kelompok mempresentasikan alur algoritma di depan kelas.",
                    'closing_activities' => "1. Siswa dan guru menyimpulkan konsep algoritma dan flowchart yang telah dipelajari.\n2. Guru memberikan refleksi dan kuis cepat melalui sistem CBT.\n3. Berdoa dan menutup pembelajaran.",
                    'diagnostic_assessment' => 'Pertanyaan lisan pemantik tentang logika langkah kerja sistem otomatis.',
                    'formative_assessment' => 'Lembar observasi unjuk kerja penyusunan flowchart kelompok di Lab Komputer.',
                    'summative_assessment' => 'Tes tertulis essay studi kasus logika algoritma dan unjuk kerja mandiri.',
                    'remedial_plan' => 'Bimbingan tutor sebaya bagi siswa yang belum mencapai Kriteria Ketercapaian TP (KKTP < 75).',
                    'enrichment_plan' => 'Tantangan membuat algoritma nested loop untuk simulasi sorting data sederhana.',
                    'student_worksheet' => "LKPD 01: Analisis Alur Transaksi Kasir Toko Buku\nInstruksi: Buatlah flowchart lengkap mencakup input barang, hitung diskon member 10%, dan cetak struk pembayaran.",
                    'assessment_rubric' => "Rubrik Penilaian:\n- Ketepatan Simbol Flowchart: Bobot 30%\n- Kelogisan Alur Pemecahan Masalah: Bobot 40%\n- Kerapian & Kerjasama Tim: Bobot 30%\nTotal Skor Maksimal: 100",
                ]);
            }
        }
    }
}
