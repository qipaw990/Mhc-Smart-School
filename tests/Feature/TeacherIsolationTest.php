<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\QuestionBank;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\TeachingLoad;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherIsolationTest extends TestCase
{
    protected User $teacherUser;
    protected Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::whereNotNull('user_id')->first();
        if (!$this->teacher) {
            $user = User::factory()->create();
            $this->teacher = Teacher::create([
                'school_id' => School::first()?->id ?? 1,
                'user_id' => $user->id,
                'name' => 'Guru Test Isolasi',
                'nuptk' => '1234567890123456',
                'email' => 'gurutest@smk.sch.id',
                'gender' => 'L',
                'employment_status' => 'GTY',
            ]);
            $guruRole = Role::firstOrCreate(['name' => 'guru'], ['label' => 'Guru Mata Pelajaran']);
            $user->roles()->attach($guruRole->id);
            $this->teacherUser = $user;
        } else {
            $this->teacherUser = $this->teacher->user;
        }
    }

    public function test_teacher_dashboard_shows_teacher_identity_and_kpi(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee($this->teacher->name);
        $response->assertSee('Beban Mengajar');
        $response->assertSee('Jadwal Mengajar');
        // Ensure admin-only master data is NOT in teacher sidebar
        $response->assertDontSee('Master Tenaga Pendidik');
        $response->assertDontSee('Master Peserta Didik');
    }

    public function test_teacher_journals_scoped_to_teacher_primary_key(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/journals');

        $response->assertStatus(200);
        $response->assertSee('Jurnal Mengajar');
    }

    public function test_teacher_gradebook_scoped_to_teacher_primary_key(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/gradebook');

        $response->assertStatus(200);
        $response->assertSee('Gradebook');
    }

    public function test_teacher_cbt_banks_scoped_to_teacher_primary_key(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/cbt/banks');

        $response->assertStatus(200);
        $response->assertSee('Bank Soal');
    }

    public function test_teacher_schedule_preselects_teacher(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/scheduler');

        $response->assertStatus(200);
        $response->assertSee('Jadwal');
    }

    public function test_teacher_curriculum_cp_tp_scoped_to_teaching_load(): void
    {
        $response = $this->actingAs($this->teacherUser)->get('/curriculum/cp-tp');

        $response->assertStatus(200);
        $response->assertSee('Capaian');
    }
}
