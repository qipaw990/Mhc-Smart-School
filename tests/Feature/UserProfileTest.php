<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('username', 'admin')->first() ?? User::factory()->create();
    }

    public function test_user_can_view_profile_edit_page(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Profil & Keamanan', false);
        $response->assertSee($this->user->name, false);
    }

    public function test_user_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->user)->put('/profile', [
            'name'  => 'Admin Updated Name',
            'email' => 'admin_updated@smkmuthiaharapanclk.com',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $this->user->id,
            'name'  => 'Admin Updated Name',
            'email' => 'admin_updated@smkmuthiaharapanclk.com',
        ]);
    }

    public function test_user_can_change_password(): void
    {
        $testUser = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($testUser)->put('/profile/password', [
            'current_password'      => 'password123',
            'password'              => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $testUser->refresh();
        $this->assertTrue(Hash::check('newsecretpassword123', $testUser->password));
    }

    public function test_password_change_fails_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)->put('/profile/password', [
            'current_password'      => 'wrongpassword',
            'password'              => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
