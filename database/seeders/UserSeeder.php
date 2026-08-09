<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $password = bcrypt('password');

        $users = [
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'email' => 'admin@mhcsmartschool.sch.id',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin Operator Sekolah',
                'username' => 'adminsekolah',
                'email' => 'operator@mhcsmartschool.sch.id',
                'role' => 'admin_sekolah',
            ],
            [
                'name' => 'Dr. H. Ahmad Rizki, M.Pd.',
                'username' => 'kepsek',
                'email' => 'kepsek@mhcsmartschool.sch.id',
                'role' => 'kepala_sekolah',
            ],
            [
                'name' => 'Dewi Sartika, S.Pd., M.T.',
                'username' => 'kurikulum',
                'email' => 'kurikulum@mhcsmartschool.sch.id',
                'role' => 'wakasek_kurikulum',
            ],
            [
                'name' => 'Bambang Pratama, S.Pd.',
                'username' => 'kesiswaan',
                'email' => 'kesiswaan@mhcsmartschool.sch.id',
                'role' => 'wakasek_kesiswaan',
            ],
            [
                'name' => 'Rina Kurnia, S.E.',
                'username' => 'bendahara',
                'email' => 'bendahara@mhcsmartschool.sch.id',
                'role' => 'bendahara',
            ],
            [
                'name' => 'Dra. Fitriani, M.Psi.',
                'username' => 'gurubk',
                'email' => 'bk@mhcsmartschool.sch.id',
                'role' => 'guru_bk',
            ],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'school_id' => $school->id,
                'name' => $u['name'],
                'username' => $u['username'],
                'email' => $u['email'],
                'password' => $password,
                'status' => 'active',
            ]);

            $role = Role::where('name', $u['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }
        }
    }
}
