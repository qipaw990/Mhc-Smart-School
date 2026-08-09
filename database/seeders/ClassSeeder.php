<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Room;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $rpl = Major::where('code', 'RPL')->first();
        $tbsm = Major::where('code', 'TBSM')->first();
        $akl = Major::where('code', 'AKL')->first();

        $teachers = Teacher::all();
        $rooms = Room::all();

        $classes = [
            ['name' => 'X RPL 1', 'grade_level' => 'X', 'major_id' => $rpl->id, 'room_id' => $rooms[0]->id ?? null, 'homeroom_teacher_id' => $teachers[0]->id ?? null],
            ['name' => 'X RPL 2', 'grade_level' => 'X', 'major_id' => $rpl->id, 'room_id' => $rooms[1]->id ?? null, 'homeroom_teacher_id' => $teachers[1]->id ?? null],
            ['name' => 'X RPL 3', 'grade_level' => 'X', 'major_id' => $rpl->id, 'room_id' => $rooms[2]->id ?? null, 'homeroom_teacher_id' => $teachers[2]->id ?? null],
            ['name' => 'XI RPL 1', 'grade_level' => 'XI', 'major_id' => $rpl->id, 'room_id' => $rooms[3]->id ?? null, 'homeroom_teacher_id' => $teachers[3]->id ?? null],
            ['name' => 'XI RPL 2', 'grade_level' => 'XI', 'major_id' => $rpl->id, 'room_id' => $rooms[4]->id ?? null, 'homeroom_teacher_id' => $teachers[4]->id ?? null],
            ['name' => 'XI RPL 3', 'grade_level' => 'XI', 'major_id' => $rpl->id, 'room_id' => $rooms[5]->id ?? null, 'homeroom_teacher_id' => null],
            ['name' => 'XII RPL 1', 'grade_level' => 'XII', 'major_id' => $rpl->id, 'room_id' => $rooms[6]->id ?? null, 'homeroom_teacher_id' => null],
            ['name' => 'X TBSM 1', 'grade_level' => 'X', 'major_id' => $tbsm->id, 'room_id' => $rooms[8]->id ?? null, 'homeroom_teacher_id' => null],
            ['name' => 'X AKL 1', 'grade_level' => 'X', 'major_id' => $akl->id, 'room_id' => $rooms[9]->id ?? null, 'homeroom_teacher_id' => null],
        ];

        foreach ($classes as $c) {
            SchoolClass::create(array_merge($c, [
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'capacity' => 36,
            ]));
        }
    }
}
