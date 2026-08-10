<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Major;
use App\Models\P5Project;
use App\Models\ReportCard;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
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

        $this->adminUser = User::where('username', 'admin')->first() ?? User::factory()->create(['username' => 'admin', 'password' => bcrypt('password')]);

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

    public function test_school_api_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        // GET /school
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/school');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        // POST /school/attendance-times
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/school/attendance-times', [
                'attendance_time_entry' => '07:00',
                'attendance_time_late'  => '07:15',
                'attendance_time_exit'  => '15:30',
            ]);
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_master_data_get_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $endpoints = [
            '/api/v1/master/academic-year',
            '/api/v1/master/classes',
            '/api/v1/master/students',
            '/api/v1/master/teachers',
            '/api/v1/master/subjects',
            '/api/v1/master/majors',
            '/api/v1/master/rooms',
        ];

        foreach ($endpoints as $url) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson($url);
            $response->assertStatus(200)->assertJsonPath('status', 'success');
        }
    }

    public function test_master_data_crud_room(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;
        $code = 'TEST-LAB-' . rand(100, 999);

        // CREATE
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/master/rooms', [
                'code'     => $code,
                'name'     => 'Lab Komputer Uji Coba',
                'type'     => 'lab',
                'capacity' => 36,
            ]);
        $response->assertStatus(201)->assertJsonPath('status', 'success');
        $roomId = $response->json('data.id');

        // UPDATE
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/v1/master/rooms/{$roomId}", [
                'code'     => $code,
                'name'     => 'Lab Komputer Uji Coba (Updated)',
                'type'     => 'lab',
                'capacity' => 40,
                'status'   => 'active',
            ]);
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        // DELETE
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/master/rooms/{$roomId}");
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_curriculum_and_atp_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/curriculum/outcomes');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/curriculum/atp');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/curriculum/materials');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/curriculum/modules');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_attendance_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/attendance/today');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/attendance/wa-logs');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_gradebook_and_rapor_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/gradebook');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/rapor');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/rapor/leger');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_p5_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/p5');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_student_portal_endpoints(): void
    {
        if (!$this->studentUser) {
            $this->markTestSkipped('No student user for portal test.');
        }

        $token = $this->studentUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/student/nilai');
        $response->assertStatus(200)->assertJsonPath('status', 'success');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/student/kehadiran');
        $response->assertStatus(200)->assertJsonPath('status', 'success');
    }

    public function test_user_management_endpoints(): void
    {
        $token = $this->adminUser->createToken('TestDevice')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users');
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

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }
}
