<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        $rpl = Major::where('code', 'RPL')->first();
        $tbsm = Major::where('code', 'TBSM')->first();
        $akl = Major::where('code', 'AKL')->first();

        $subjects = [
            // Muatan Umum
            [
                'code' => 'PAI',
                'name' => 'Pendidikan Agama Islam & Budi Pekerti',
                'group' => 'A_general',
                'phase' => 'E',
                'type' => 'theory',
                'hours_per_week' => 3,
                'total_hours' => 54,
                'major_id' => null,
            ],
            [
                'code' => 'MTK',
                'name' => 'Matematika Terapan',
                'group' => 'A_general',
                'phase' => 'E',
                'type' => 'theory_practice',
                'hours_per_week' => 4,
                'total_hours' => 72,
                'major_id' => null,
            ],
            [
                'code' => 'BIND',
                'name' => 'Bahasa Indonesia',
                'group' => 'A_general',
                'phase' => 'E',
                'type' => 'theory',
                'hours_per_week' => 4,
                'total_hours' => 72,
                'major_id' => null,
            ],
            [
                'code' => 'BING',
                'name' => 'Bahasa Inggris & Komunikasi Bisnis',
                'group' => 'A_general',
                'phase' => 'E',
                'type' => 'theory_practice',
                'hours_per_week' => 4,
                'total_hours' => 72,
                'major_id' => null,
            ],

            // Kejuruan RPL
            [
                'code' => 'DASAR-RPL',
                'name' => 'Dasar-Dasar Pengembangan Perangkat Lunak & Gim',
                'group' => 'B_vocational',
                'phase' => 'E',
                'type' => 'theory_practice',
                'hours_per_week' => 6,
                'total_hours' => 108,
                'major_id' => $rpl?->id,
            ],
            [
                'code' => 'WEB-DEV',
                'name' => 'Pemrograman Web & Perangkat Bergerak',
                'group' => 'C_concentration',
                'phase' => 'F',
                'type' => 'theory_practice',
                'hours_per_week' => 8,
                'total_hours' => 144,
                'major_id' => $rpl?->id,
            ],
            [
                'code' => 'BASIS-DATA',
                'name' => 'Basis Data & Cloud Architecture',
                'group' => 'C_concentration',
                'phase' => 'F',
                'type' => 'theory_practice',
                'hours_per_week' => 6,
                'total_hours' => 108,
                'major_id' => $rpl?->id,
            ],

            // Kejuruan TBSM
            [
                'code' => 'DASAR-OTOMOTIF',
                'name' => 'Dasar-Dasar Otomotif Sepeda Motor',
                'group' => 'B_vocational',
                'phase' => 'E',
                'type' => 'practice',
                'hours_per_week' => 6,
                'total_hours' => 108,
                'major_id' => $tbsm?->id,
            ],

            // Kejuruan AKL
            [
                'code' => 'DASAR-AKUNTANSI',
                'name' => 'Dasar-Dasar Akuntansi & Keuangan Lembaga',
                'group' => 'B_vocational',
                'phase' => 'E',
                'type' => 'theory_practice',
                'hours_per_week' => 6,
                'total_hours' => 108,
                'major_id' => $akl?->id,
            ],
        ];

        foreach ($subjects as $s) {
            Subject::create(array_merge($s, [
                'school_id' => $school->id,
                'status' => 'active',
            ]));
        }
    }
}
