<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AndroidApiTest extends TestCase
{
    protected User $adminUser;
    protected User $teacherUser;
    protected User $studentUser;
    protected Teacher $teacher;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('username', 'admin')->first();

        // Find or create sample teacher
        $this->teacher = Teacher::with('user')->first();
        $this->teacherUser = $this->teacher?->user ?? User::factory()->create();

        // Find or create sample student
        $this->student = Student::with('user')->first();
        $this->studentUser = $this->student?->user ?? User::factory()->create();
    }

    public function test_admin_can_login_via_api_using_username(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'       => 'admin',
            'password'    => 'password',
            'device_name' => 'Pixel 8 Pro',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'token_type',
                'user' => [
                    'id', 'name', 'username', 'email', 'role', 'role_label',
                ],
            ],
        ]);
    }

    public function test_teacher_can_login_via_api_using_nip_or_username(): void
    {
        if (!$this->teacher) {
            $this->markTestSkipped('Teacher record not found in test DB.');
        }

        $loginIdentifier = $this->teacher->nip ?: $this->teacherUser->username;

        $response = $this->postJson('/api/v1/auth/login', [
            'login'       => $loginIdentifier,
            'password'    => 'password',
            'device_name' => 'Samsung Galaxy S24',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.user.role', 'guru');
    }

    public function test_student_can_login_via_api_using_nisn(): void
    {
        if (!$this->student) {
            $this->markTestSkipped('Student record not found in test DB.');
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'login'       => $this->student->nisn,
            'password'    => 'password',
            'device_name' => 'Xiaomi Redmi Note 13',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.user.role', 'siswa');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'admin',
            'password' => 'wrongpassword123',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('status', 'error');
    }

    public function test_authenticated_user_can_get_profile_me(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.username', $this->adminUser->username);
    }

    public function test_authenticated_user_can_check_today_attendance(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/attendance/today');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
    }

    public function test_authenticated_user_can_access_dashboard_api(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure(['data' => ['metrics', 'active_exams', 'recent_journals']]);
    }

    public function test_authenticated_user_can_access_master_data_apis(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/master/classes');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/master/students');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/master/teachers');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_authenticated_user_can_access_journals_and_cbt_apis(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/journals');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/cbt/exams');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_authenticated_user_can_access_schedule_and_curriculum_apis(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/schedule/my-schedule');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/curriculum/outcomes');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/curriculum/materials');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $tokenObj = $this->adminUser->createToken('TestDevice');
        $plainToken = $tokenObj->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        // Token record should no longer exist in database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }
}
