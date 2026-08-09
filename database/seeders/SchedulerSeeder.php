<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Room;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingLoad;
use App\Models\TimeSlot;
use App\Services\SchedulerEngineService;
use Illuminate\Database\Seeder;

class SchedulerSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();

        // 1. Time Slots (Senin - Jumat, 10 Period per hari)
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $periods = [
            1 => ['07:00', '07:45', false, 'Jam Ke-1'],
            2 => ['07:45', '08:30', false, 'Jam Ke-2'],
            3 => ['08:30', '09:15', false, 'Jam Ke-3'],
            4 => ['09:15', '09:45', true, 'Istirahat Pagi'],
            5 => ['09:45', '10:30', false, 'Jam Ke-4'],
            6 => ['10:30', '11:15', false, 'Jam Ke-5'],
            7 => ['11:15', '12:00', false, 'Jam Ke-6'],
            8 => ['12:00', '12:45', true, 'Istirahat / Sholat Dhuhur'],
            9 => ['12:45', '13:30', false, 'Jam Ke-7'],
            10 => ['13:30', '14:15', false, 'Jam Ke-8'],
        ];

        foreach ($days as $day) {
            foreach ($periods as $p => $info) {
                TimeSlot::create([
                    'day' => $day,
                    'period' => $p,
                    'start_time' => $info[0],
                    'end_time' => $info[1],
                    'is_break' => $info[2],
                    'label' => $info[3],
                ]);
            }
        }

        // 2. Teaching Loads (Pemetaan Beban Mengajar Guru -> Mapel -> Kelas)
        $teacherBudi = Teacher::where('name', 'like', '%Budi%')->first();
        $teacherSiti = Teacher::where('name', 'like', '%Siti%')->first();
        $teacherRizki = Teacher::where('name', 'like', '%Rizki%')->first();
        $teacherHendra = Teacher::where('name', 'like', '%Hendra%')->first();
        $teacherAnita = Teacher::where('name', 'like', '%Anita%')->first();

        $subjectDasarRpl = Subject::where('code', 'DASAR-RPL')->first();
        $subjectWebDev = Subject::where('code', 'WEB-DEV')->first();
        $subjectMtk = Subject::where('code', 'MTK')->first();
        $subjectBing = Subject::where('code', 'BING')->first();
        $subjectOtomotif = Subject::where('code', 'DASAR-OTOMOTIF')->first();

        $classXRpl1 = SchoolClass::where('name', 'X RPL 1')->first();
        $classXRpl2 = SchoolClass::where('name', 'X RPL 2')->first();
        $classXTbsm1 = SchoolClass::where('name', 'X TBSM 1')->first();
        $classXiRpl1 = SchoolClass::where('name', 'XI RPL 1')->first();

        $labRpl1 = Room::where('code', 'LAB-RPL-1')->first();
        $bengkelTbsm = Room::where('code', 'BENGKEL-TBSM')->first();

        $loads = [];

        // Budi: Dasar RPL X RPL 1 & X RPL 2 (6 JP masing-masing di Lab RPL)
        if ($teacherBudi && $subjectDasarRpl) {
            if ($classXRpl1) $loads[] = ['teacher' => $teacherBudi, 'subject' => $subjectDasarRpl, 'class' => $classXRpl1, 'hours' => 6, 'room' => $labRpl1];
            if ($classXRpl2) $loads[] = ['teacher' => $teacherBudi, 'subject' => $subjectDasarRpl, 'class' => $classXRpl2, 'hours' => 6, 'room' => $labRpl1];
        }

        // Rizki: Web Dev XI RPL 1 (8 JP di Lab RPL)
        if ($teacherRizki && $subjectWebDev && $classXiRpl1) {
            $loads[] = ['teacher' => $teacherRizki, 'subject' => $subjectWebDev, 'class' => $classXiRpl1, 'hours' => 8, 'room' => $labRpl1];
        }

        // Siti: Matematika X RPL 1 & X RPL 2 (4 JP)
        if ($teacherSiti && $subjectMtk) {
            if ($classXRpl1) $loads[] = ['teacher' => $teacherSiti, 'subject' => $subjectMtk, 'class' => $classXRpl1, 'hours' => 4, 'room' => null];
            if ($classXRpl2) $loads[] = ['teacher' => $teacherSiti, 'subject' => $subjectMtk, 'class' => $classXRpl2, 'hours' => 4, 'room' => null];
        }

        // Anita: Bahasa Inggris X RPL 1 & X TBSM 1 (4 JP)
        if ($teacherAnita && $subjectBing) {
            if ($classXRpl1) $loads[] = ['teacher' => $teacherAnita, 'subject' => $subjectBing, 'class' => $classXRpl1, 'hours' => 4, 'room' => null];
            if ($classXTbsm1) $loads[] = ['teacher' => $teacherAnita, 'subject' => $subjectBing, 'class' => $classXTbsm1, 'hours' => 4, 'room' => null];
        }

        // Hendra: Dasar Otomotif X TBSM 1 (6 JP di Bengkel)
        if ($teacherHendra && $subjectOtomotif && $classXTbsm1) {
            $loads[] = ['teacher' => $teacherHendra, 'subject' => $subjectOtomotif, 'class' => $classXTbsm1, 'hours' => 6, 'room' => $bengkelTbsm];
        }

        foreach ($loads as $l) {
            TeachingLoad::create([
                'school_id' => $school->id,
                'academic_year_id' => $ay->id,
                'semester_id' => $semester?->id,
                'teacher_id' => $l['teacher']->id,
                'subject_id' => $l['subject']->id,
                'class_id' => $l['class']->id,
                'hours_per_week' => $l['hours'],
                'preferred_room_id' => $l['room']?->id,
            ]);
        }

        // 3. Trigger Automatic CSP Scheduling Engine to generate initial active timetable
        $engine = new SchedulerEngineService();
        $engine->generateSchedule($ay->id, $semester?->id, 'Jadwal Reguler Semester Ganjil');
    }
}
