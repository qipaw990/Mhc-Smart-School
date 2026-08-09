<?php

namespace Database\Seeders;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        $ay = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-13',
            'end_date' => '2027-06-25',
            'is_active' => true,
        ]);

        Semester::create([
            'academic_year_id' => $ay->id,
            'name' => 'Ganjil',
            'semester_number' => 1,
            'is_active' => true,
        ]);

        Semester::create([
            'academic_year_id' => $ay->id,
            'name' => 'Genap',
            'semester_number' => 2,
            'is_active' => false,
        ]);

        // Academic Calendar sample events
        AcademicCalendar::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
            'title' => 'Masa Pengenalan Lingkungan Sekolah (MPLS)',
            'description' => 'Kegiatan MPLS untuk siswa baru kelas X tahun ajaran 2026/2027',
            'event_type' => 'mpls' ?? 'agenda',
            'start_date' => '2026-07-13',
            'end_date' => '2026-07-17',
            'color' => '#36b9cc',
        ]);

        AcademicCalendar::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
            'title' => 'Asesmen Sumatif Tengah Semester (ASTS)',
            'description' => 'Pelaksanaan Ujian Tengah Semester Ganjil via CBT',
            'event_type' => 'exam',
            'start_date' => '2026-09-21',
            'end_date' => '2026-09-26',
            'color' => '#e74a3b',
        ]);

        AcademicCalendar::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
            'title' => 'Pembagian Rapor Semester Ganjil',
            'description' => 'Penyerahan e-Rapor Kurikulum Merdeka kepada orang tua siswa',
            'event_type' => 'report',
            'start_date' => '2026-12-18',
            'end_date' => '2026-12-18',
            'color' => '#1cc88a',
        ]);
    }
}
