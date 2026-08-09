<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'name'           => 'SMK MUTHIA HARAPAN CICALENGKA',
            'npsn'           => '69725846',
            'nss'            => '342022201018',
            'address'        => 'Jl. Babakan Peuteuy No. 300',
            'village'        => 'Babakanpeuteuy',
            'district'       => 'Cicalengka',
            'regency'        => 'Kabupaten Bandung',
            'province'       => 'Jawa Barat',
            'postal_code'    => '40395',
            'phone'          => '081314654347',
            'email'          => 'smk.muthia_harapan@yahoo.com',
            'website'        => 'http://www.smkmuthiaharapanclk.sch.id',
            'logo'           => null,
            'principal_name' => 'Kepala SMK Muthia Harapan',
            'accreditation'  => 'A',
            'vision'         => 'Mewujudkan SMK Muthia Harapan Cicalengka yang unggul, berkarakter, kompeten, dan berdaya saing di tingkat nasional maupun internasional.',
            'mission'        => implode("\n", [
                '1. Menyelenggarakan pendidikan dan pelatihan vokasi yang berkualitas sesuai kebutuhan industri.',
                '2. Mengembangkan potensi peserta didik agar menjadi insan yang beriman, bertaqwa, dan berkarakter Profil Pelajar Pancasila.',
                '3. Menjalin kemitraan strategis dengan dunia usaha dan dunia industri (DUDI).',
                '4. Mewujudkan pengelolaan sekolah yang transparan, akuntabel, dan berbasis teknologi digital.',
            ]),
        ];

        $school = School::where('school_code', 'MHC01')->first();
        if ($school) {
            $school->update($data);
        } else {
            School::create(array_merge(['school_code' => 'MHC01'], $data));
        }
    }
}
