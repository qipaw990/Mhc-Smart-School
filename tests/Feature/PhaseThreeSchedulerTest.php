<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingLoad;
use App\Models\User;
use App\Services\SchedulerEngineService;
use Tests\TestCase;

class PhaseThreeSchedulerTest extends TestCase
{
    protected $admin;
    protected ?int $generatedScheduleId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('username', 'admin')->first();
    }

    protected function tearDown(): void
    {
        // Cleanup: if a test-generated schedule was created, delete it and restore
        // the previously active schedule so production data is not disturbed.
        if ($this->generatedScheduleId) {
            $generated = Schedule::find($this->generatedScheduleId);
            if ($generated) {
                $ay = AcademicYear::find($generated->academic_year_id);

                // Restore the most recent non-test schedule back to active
                Schedule::where('academic_year_id', $generated->academic_year_id)
                    ->where('id', '!=', $this->generatedScheduleId)
                    ->whereIn('status', ['archived'])
                    ->orderByDesc('id')
                    ->limit(1)
                    ->update(['status' => 'active']);

                // Hard-delete generated items and schedule
                $generated->items()->delete();
                $generated->forceDelete();
            }
            $this->generatedScheduleId = null;
        }

        // Remove any test teaching load (has hours_per_week=4 and was added by test)
        TeachingLoad::where('hours_per_week', 4)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereHas('teacher', fn($q) => $q->where('id', Teacher::first()?->id ?? 0))
            ->delete();

        parent::tearDown();
    }

    public function test_scheduler_matrix_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/scheduler')
            ->assertStatus(200)
            ->assertSee('Matriks Jadwal Pelajaran Sekolah', false);
    }

    public function test_teaching_loads_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/scheduler/loads')
            ->assertStatus(200)
            ->assertSee('Pemetaan Beban Mengajar Guru & Rombel', false);
    }

    public function test_scheduler_generator_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/scheduler/generator')
            ->assertStatus(200)
            ->assertSee('Automatic School Scheduler Engine', false);
    }

    public function test_conflict_detector_page(): void
    {
        $this->actingAs($this->admin)
            ->get('/scheduler/conflicts')
            ->assertStatus(200)
            ->assertSee('Conflict Detector', false);
    }

    public function test_can_add_teaching_load(): void
    {
        $teacher = Teacher::first();
        $subject = Subject::first();
        $class = SchoolClass::first();

        $response = $this->actingAs($this->admin)->post('/scheduler/loads', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'hours_per_week' => 4,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teaching_loads', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
        ]);

        // Cleanup immediately after assertion
        TeachingLoad::where('teacher_id', $teacher->id)
            ->where('subject_id', $subject->id)
            ->where('class_id', $class->id)
            ->where('hours_per_week', 4)
            ->orderByDesc('id')
            ->limit(1)
            ->delete();
    }

    public function test_scheduler_engine_generates_valid_schedule_without_hard_conflicts(): void
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $engine = new SchedulerEngineService();

        $result = $engine->generateSchedule($ay->id, null, 'Jadwal Unit Test', $this->admin->id);

        $this->assertEquals('success', $result['status']);
        $this->assertGreaterThan(0, $result['total_items']);
        $this->assertGreaterThan(0, $result['optimization_score']);

        // Track for cleanup in tearDown
        $this->generatedScheduleId = $result['schedule']->id;

        // Check conflicts
        $conflicts = $engine->detectConflicts($result['schedule']->id);
        $this->assertFalse($conflicts['has_conflicts']);
        $this->assertEquals(0, $conflicts['total_conflicts']);
    }
}
