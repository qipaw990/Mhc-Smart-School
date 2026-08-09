<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PhaseOneMasterDataTest extends TestCase
{
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SMK MUTHIA HARAPAN CICALENGKA');
    }

    public function test_super_admin_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_student_can_login_with_nisn(): void
    {
        $response = $this->post('/login', [
            'login' => '0061234501',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_teacher_can_login_with_nip(): void
    {
        $response = $this->post('/login', [
            'login' => '198501152010011001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_teacher_can_login_with_nuptk(): void
    {
        $teacher = \App\Models\Teacher::whereNotNull('nuptk')->first();
        if ($teacher) {
            $response = $this->post('/login', [
                'login' => $teacher->nuptk,
                'password' => 'password',
            ]);

            $response->assertRedirect('/dashboard');
            $this->assertAuthenticated();
        }
    }

    public function test_authenticated_admin_can_access_dashboard_and_master_data(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Pengumuman', false);

        $this->actingAs($admin)
            ->get('/master/school')
            ->assertStatus(200)
            ->assertSee('Profil', false);

        $this->actingAs($admin)
            ->get('/master/academic-year')
            ->assertStatus(200)
            ->assertSee('Tahun Ajaran', false);

        $this->actingAs($admin)
            ->get('/master/majors')
            ->assertStatus(200)
            ->assertSee('Rekayasa Perangkat Lunak');

        $this->actingAs($admin)
            ->get('/master/rooms')
            ->assertStatus(200)
            ->assertSee('Master Ruangan');

        $this->actingAs($admin)
            ->get('/master/classes')
            ->assertStatus(200)
            ->assertSee('X RPL 1');

        $this->actingAs($admin)
            ->get('/master/teachers')
            ->assertStatus(200)
            ->assertSee('Budi Santoso');

        $this->actingAs($admin)
            ->get('/master/students')
            ->assertStatus(200)
            ->assertSee('Andi Pratama');
    }

    public function test_admin_can_download_templates_for_master_data(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)
            ->get('/master/teachers/template')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $this->actingAs($admin)
            ->get('/master/students/template')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $this->actingAs($admin)
            ->get('/master/classes/template')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $this->actingAs($admin)
            ->get('/curriculum/subjects/template')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
    }
}
