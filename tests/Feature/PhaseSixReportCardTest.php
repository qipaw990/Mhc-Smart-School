<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\P5Project;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\ReportCardGeneratorService;
use Tests\TestCase;

class PhaseSixReportCardTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->first();
    }

    public function test_rapor_index_and_show(): void
    {
        $this->actingAs($this->admin)
            ->get('/rapor')
            ->assertStatus(200)
            ->assertSee('E-Rapor Kurikulum Merdeka (ONE DATA SCHOOL)', false);

        $reportCard = ReportCard::first();
        if ($reportCard) {
            $this->actingAs($this->admin)
                ->get('/rapor/' . $reportCard->id)
                ->assertStatus(200)
                ->assertSee('Nilai Capaian Hasil Belajar');

            $this->actingAs($this->admin)
                ->get('/rapor/' . $reportCard->id . '/print')
                ->assertStatus(200)
                ->assertSee('LAPORAN HASIL BELAJAR (RAPOR AKADEMIK)');
        }
    }

    public function test_leger_nilai_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/rapor/leger')
            ->assertStatus(200)
            ->assertSee('Leger Nilai Semester', false);
    }

    public function test_can_generate_rapor_for_class(): void
    {
        $class = SchoolClass::first();
        $ay = AcademicYear::where('is_active', true)->first();

        $response = $this->actingAs($this->admin)->post('/rapor/generate', [
            'class_id' => $class->id,
        ]);

        $response->assertRedirect('/rapor?class_id=' . $class->id);
        $this->assertDatabaseHas('report_cards', [
            'class_id' => $class->id,
            'academic_year_id' => $ay->id,
        ]);
    }

    public function test_p5_projects_index_scores_and_print(): void
    {
        $this->actingAs($this->admin)
            ->get('/p5')
            ->assertStatus(200)
            ->assertSee('Rapor Projek Profil Pelajar Pancasila (P5)', false);

        $this->actingAs($this->admin)
            ->get('/p5/create')
            ->assertStatus(200)
            ->assertSee('Rancang Modul Projek P5 Baru', false);

        $project = P5Project::first();
        if ($project) {
            $this->actingAs($this->admin)
                ->get('/p5/' . $project->id . '/scores')
                ->assertStatus(200)
                ->assertSee('Matriks Penilaian Dimensi Siswa');

            $student = Student::where('current_class_id', $project->class_id)->first();
            if ($student) {
                $this->actingAs($this->admin)
                    ->get('/p5/' . $project->id . '/print/' . $student->id)
                    ->assertStatus(200)
                    ->assertSee('RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)');
            }
        }
    }
}
