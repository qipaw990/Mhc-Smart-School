<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\School;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        $majors = [
            [
                'code' => 'RPL',
                'name' => 'Rekayasa Perangkat Lunak',
                'head_of_major' => 'Budi Santoso, S.Kom., M.T.',
                'description' => 'Kompetensi Keahlian Pengembangan Web, Mobile, Cloud, dan AI Engineering.',
            ],
            [
                'code' => 'TBSM',
                'name' => 'Teknik & Bisnis Sepeda Motor',
                'head_of_major' => 'Ir. Hendra Wijaya',
                'description' => 'Kompetensi Keahlian Pemeliharaan & Perbaikan Otomotif Sepeda Motor Modern.',
            ],
            [
                'code' => 'AKL',
                'name' => 'Akuntansi & Keuangan Lembaga',
                'head_of_major' => 'Siti Nurhaliza, S.E., M.Ak.',
                'description' => 'Kompetensi Keahlian Pengelolaan Keuangan Digital, Akuntansi Publik, dan Perbankan.',
            ],
        ];

        foreach ($majors as $m) {
            Major::create(array_merge($m, ['school_id' => $school->id, 'status' => 'active']));
        }
    }
}
