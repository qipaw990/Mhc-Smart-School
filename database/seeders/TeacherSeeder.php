<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $password = bcrypt('password');

        $guruRole = Role::where('name', 'guru')->first();
        $waliRole = Role::where('name', 'wali_kelas')->first();

        $teachers = [
            [
                'nip' => '198501152010011001',
                'name' => 'Budi Santoso',
                'title_prefix' => '',
                'title_suffix' => 'S.Kom., M.T.',
                'username' => 'budis',
                'email' => 'budis@mhcsmartschool.sch.id',
                'gender' => 'L',
                'employment_status' => 'PNS',
                'position' => 'Kaprog RPL / Guru Pemrograman',
                'is_wali' => true,
            ],
            [
                'nip' => '198803202014022002',
                'name' => 'Siti Nurhaliza',
                'title_prefix' => '',
                'title_suffix' => 'S.Pd.',
                'username' => 'sitin',
                'email' => 'sitin@mhcsmartschool.sch.id',
                'gender' => 'P',
                'employment_status' => 'PNS',
                'position' => 'Guru Matematika',
                'is_wali' => true,
            ],
            [
                'nip' => '199207102019031003',
                'name' => 'Rizki Ramadhan',
                'title_prefix' => '',
                'title_suffix' => 'S.Kom.',
                'username' => 'rizkir',
                'email' => 'rizkir@mhcsmartschool.sch.id',
                'gender' => 'L',
                'employment_status' => 'PPPK',
                'position' => 'Guru Web & Mobile Dev',
                'is_wali' => true,
            ],
            [
                'nip' => '199011122018011004',
                'name' => 'Hendra Wijaya',
                'title_prefix' => 'Ir.',
                'title_suffix' => 'M.T.',
                'username' => 'hendraw',
                'email' => 'hendraw@mhcsmartschool.sch.id',
                'gender' => 'L',
                'employment_status' => 'PNS',
                'position' => 'Kaprog TBSM',
                'is_wali' => false,
            ],
            [
                'nip' => '199404182020122005',
                'name' => 'Anita Rahmawati',
                'title_prefix' => '',
                'title_suffix' => 'S.Pd.',
                'username' => 'anitar',
                'email' => 'anitar@mhcsmartschool.sch.id',
                'gender' => 'P',
                'employment_status' => 'GTY',
                'position' => 'Guru Bahasa Inggris',
                'is_wali' => false,
            ],
        ];

        foreach ($teachers as $t) {
            $user = User::create([
                'school_id' => $school->id,
                'name' => $t['name'] . ($t['title_suffix'] ? ', ' . $t['title_suffix'] : ''),
                'username' => $t['username'],
                'email' => $t['email'],
                'password' => $password,
                'status' => 'active',
            ]);

            $user->roles()->attach($guruRole->id);
            if ($t['is_wali'] && $waliRole) {
                $user->roles()->attach($waliRole->id);
            }

            Teacher::create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'nip' => $t['nip'],
                'nuptk' => 'NUPTK' . substr($t['nip'], 0, 12),
                'nik' => '3201' . substr($t['nip'], 0, 12),
                'name' => $t['name'],
                'title_prefix' => $t['title_prefix'],
                'title_suffix' => $t['title_suffix'],
                'gender' => $t['gender'],
                'birth_place' => 'Bogor',
                'birth_date' => '1985-05-15',
                'address' => 'Jl. Pemuda No. 12 Bogor',
                'phone' => '0812345678' . rand(10, 99),
                'email' => $t['email'],
                'education' => 'S1 / S2',
                'major' => 'Pendidikan',
                'employment_status' => $t['employment_status'],
                'position' => $t['position'],
            ]);
        }
    }
}
