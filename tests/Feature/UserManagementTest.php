<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    protected User $admin;
    protected Role $teacherRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('username', 'admin')->first() ?? User::factory()->create();
        $this->teacherRole = Role::firstOrCreate(['name' => 'guru'], ['label' => 'Guru Mata Pelajaran']);
    }

    public function test_admin_can_access_user_management_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna & Hak Akses');
        $response->assertSee('PENGGUNA');
    }

    public function test_admin_can_create_new_user(): void
    {
        $uid = uniqid();
        $username = 'guru_' . $uid;
        $email = "guru_{$uid}@smk.sch.id";

        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Guru Baru Test ' . $uid,
            'username' => $username,
            'email' => $email,
            'password' => 'password123',
            'phone' => '081234567890',
            'role_id' => $this->teacherRole->id,
            'status' => 'active',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'username' => $username,
            'email' => $email,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $uid = uniqid();
        $user = User::create([
            'name' => 'User Edit ' . $uid,
            'username' => 'user_edit_' . $uid,
            'email' => "user_edit_{$uid}@smk.sch.id",
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $newUsername = 'user_upd_' . $uid;
        $newEmail = "user_upd_{$uid}@smk.sch.id";

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'User Updated ' . $uid,
            'username' => $newUsername,
            'email' => $newEmail,
            'phone' => '0899999999',
            'role_id' => $this->teacherRole->id,
            'status' => 'inactive',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'User Updated ' . $uid,
            'username' => $newUsername,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $uid = uniqid();
        $user = User::create([
            'name' => 'User PW ' . $uid,
            'username' => 'user_pw_' . $uid,
            'email' => "user_pw_{$uid}@smk.sch.id",
            'password' => Hash::make('oldpassword'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}/reset-password", [
            'new_password' => 'newsecretpass123',
        ]);

        $response->assertRedirect('/users');
        $user->refresh();
        $this->assertTrue(Hash::check('newsecretpass123', $user->password));
    }

    public function test_admin_can_delete_user(): void
    {
        $uid = uniqid();
        $user = User::create([
            'name' => 'User Del ' . $uid,
            'username' => 'user_del_' . $uid,
            'email' => "user_del_{$uid}@smk.sch.id",
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete("/users/{$user->id}");

        $response->assertRedirect('/users');
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
}
