<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingModule;
use App\Models\User;
use Tests\TestCase;

class PhaseTwoCurriculumTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->first();
    }

    public function test_subjects_index_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/curriculum/subjects')
            ->assertStatus(200)
            ->assertSee('Struktur Kurikulum & Mata Pelajaran', false)
            ->assertSee('WEB-DEV');
    }

    public function test_cp_and_tp_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/curriculum/cp-tp')
            ->assertStatus(200)
            ->assertSee('Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP)', false);
    }

    public function test_atp_timeline_builder_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/curriculum/atp')
            ->assertStatus(200)
            ->assertSee('Alur Tujuan Pembelajaran (ATP) Timeline Builder', false);
    }

    public function test_materials_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/curriculum/materials')
            ->assertStatus(200)
            ->assertSee('Materi & Sumber Belajar Digital', false);
    }

    public function test_teaching_modules_list_and_generator_pages(): void
    {
        $this->actingAs($this->admin)
            ->get('/curriculum/modules')
            ->assertStatus(200)
            ->assertSee('Modul Ajar Generator Kurikulum Merdeka', false);

        $this->actingAs($this->admin)
            ->get('/curriculum/modules/create')
            ->assertStatus(200)
            ->assertSee('Generator Modul Ajar Kurikulum Merdeka SMK', false);

        $module = TeachingModule::first();
        if ($module) {
            $this->actingAs($this->admin)
                ->get('/curriculum/modules/' . $module->id)
                ->assertStatus(200)
                ->assertSee('MODUL AJAR KURIKULUM MERDEKA');

            $this->actingAs($this->admin)
                ->get('/curriculum/modules/' . $module->id . '/print')
                ->assertStatus(200)
                ->assertSee('MODUL AJAR KURIKULUM MERDEKA');
        }
    }

    public function test_can_create_new_cp_and_tp(): void
    {
        $subject = Subject::first();
        $ay = AcademicYear::where('is_active', true)->first();

        $cpResponse = $this->actingAs($this->admin)->post('/curriculum/cp', [
            'subject_id' => $subject->id,
            'phase' => $subject->phase,
            'code' => 'CP-TEST-01',
            'element' => 'Elemen Test Kurikulum',
            'description' => 'Deskripsi capaian pembelajaran test',
        ]);
        $cpResponse->assertRedirect();

        $cp = LearningOutcome::where('code', 'CP-TEST-01')->first();
        $this->assertNotNull($cp);

        $tpResponse = $this->actingAs($this->admin)->post('/curriculum/tp', [
            'learning_outcome_id' => $cp->id,
            'code' => 'TP-TEST-01.1',
            'order_number' => 1,
            'description' => 'Deskripsi tujuan pembelajaran test',
            'semester_number' => 1,
            'estimated_hours' => 4,
        ]);
        $tpResponse->assertRedirect();

        $tp = LearningObjective::where('code', 'TP-TEST-01.1')->first();
        $this->assertNotNull($tp);
    }
}
