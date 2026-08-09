<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            AcademicYearSeeder::class,
            MajorSeeder::class,
            RoomSeeder::class,
            TeacherSeeder::class,
            ClassSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            CurriculumSeeder::class,
            SchedulerSeeder::class,
            AttendanceAndGradebookSeeder::class,
            CbtSeeder::class,
            ReportCardSeeder::class,
        ]);
    }
}
