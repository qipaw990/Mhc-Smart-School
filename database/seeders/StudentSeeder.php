<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentHistory;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $classXRpl1 = SchoolClass::where('name', 'X RPL 1')->first();
        $classXRpl2 = SchoolClass::where('name', 'X RPL 2')->first();
        $rplMajor = Major::where('code', 'RPL')->first();

        $siswaRole = Role::where('name', 'siswa')->first();
        $ortuRole = Role::where('name', 'orang_tua')->first();
        $password = bcrypt('password');

        $sampleStudents = [
            ['nis' => '20261001', 'nisn' => '0061234501', 'name' => 'Andi Pratama', 'gender' => 'L', 'class' => $classXRpl1],
            ['nis' => '20261002', 'nisn' => '0061234502', 'name' => 'Budi Setiawan', 'gender' => 'L', 'class' => $classXRpl1],
            ['nis' => '20261003', 'nisn' => '0061234503', 'name' => 'Citra Lestari', 'gender' => 'P', 'class' => $classXRpl1],
            ['nis' => '20261004', 'nisn' => '0061234504', 'name' => 'Dwi Nuraini', 'gender' => 'P', 'class' => $classXRpl1],
            ['nis' => '20261005', 'nisn' => '0061234505', 'name' => 'Eko Prasetyo', 'gender' => 'L', 'class' => $classXRpl1],
            ['nis' => '20261006', 'nisn' => '0061234506', 'name' => 'Fajar Hidayat', 'gender' => 'L', 'class' => $classXRpl2],
            ['nis' => '20261007', 'nisn' => '0061234507', 'name' => 'Gita Gutawa', 'gender' => 'P', 'class' => $classXRpl2],
            ['nis' => '20261008', 'nisn' => '0061234508', 'name' => 'Hasan Basri', 'gender' => 'L', 'class' => $classXRpl2],
            ['nis' => '20261009', 'nisn' => '0061234509', 'name' => 'Indah Permata', 'gender' => 'P', 'class' => $classXRpl2],
            ['nis' => '20261010', 'nisn' => '0061234510', 'name' => 'Joko Widodo', 'gender' => 'L', 'class' => $classXRpl2],
        ];

        foreach ($sampleStudents as $s) {
            // Create user account for student
            $studentUser = User::create([
                'school_id' => $school->id,
                'name' => $s['name'],
                'username' => $s['nisn'],
                'email' => strtolower(str_replace(' ', '', $s['name'])) . '@siswa.mhc.sch.id',
                'password' => $password,
                'status' => 'active',
            ]);
            $studentUser->roles()->attach($siswaRole->id);

            // Create student entity
            $student = Student::create([
                'school_id' => $school->id,
                'user_id' => $studentUser->id,
                'current_class_id' => $s['class']->id,
                'major_id' => $rplMajor->id,
                'nis' => $s['nis'],
                'nisn' => $s['nisn'],
                'nik' => '3201000' . $s['nis'],
                'name' => $s['name'],
                'gender' => $s['gender'],
                'birth_place' => 'Bogor',
                'birth_date' => '2009-08-15',
                'religion' => 'Islam',
                'address' => 'Jl. Pajajaran No. ' . rand(1, 100) . ' Bogor',
                'phone' => '0896' . rand(10000000, 99999999),
                'parent_name' => 'Bapak/Ibu ' . $s['name'],
                'parent_phone' => '0812' . rand(10000000, 99999999),
                'email' => $studentUser->email,
                'entry_year' => 2026,
                'status' => 'active',
            ]);

            // Create parent account for student
            $parentUser = User::create([
                'school_id' => $school->id,
                'name' => 'Orang Tua ' . $s['name'],
                'username' => 'ortu_' . $s['nisn'],
                'email' => 'ortu_' . strtolower(str_replace(' ', '', $s['name'])) . '@gmail.com',
                'password' => $password,
                'status' => 'active',
            ]);
            $parentUser->roles()->attach($ortuRole->id);

            $parent = StudentParent::create([
                'user_id' => $parentUser->id,
                'father_name' => 'Bapak ' . $s['name'],
                'father_phone' => '0813' . rand(10000000, 99999999),
                'mother_name' => 'Ibu ' . $s['name'],
                'mother_phone' => '0812' . rand(10000000, 99999999),
                'address' => $student->address,
            ]);

            $student->parents()->attach($parent->id, ['relationship' => 'father']);

            // Create student history
            StudentHistory::create([
                'student_id' => $student->id,
                'academic_year_id' => $ay->id,
                'class_id' => $s['class']->id,
                'action' => 'enrolled',
                'notes' => 'Diterima di kelas ' . $s['class']->name . ' melalui PPDB 2026',
            ]);
        }
    }
}
