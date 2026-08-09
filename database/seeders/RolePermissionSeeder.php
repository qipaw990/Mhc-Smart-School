<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'admin_sekolah' => 'Admin Sekolah',
            'kepala_sekolah' => 'Kepala Sekolah',
            'wakasek_kurikulum' => 'Wakasek Kurikulum',
            'wakasek_kesiswaan' => 'Wakasek Kesiswaan',
            'wakasek_sarpras' => 'Wakasek Sarpras',
            'wakasek_humas' => 'Wakasek Humas/Hubin',
            'tata_usaha' => 'Tata Usaha',
            'bendahara' => 'Bendahara',
            'guru' => 'Guru Mata Pelajaran',
            'wali_kelas' => 'Wali Kelas',
            'guru_bk' => 'Guru BK',
            'guru_piket' => 'Guru Piket',
            'operator' => 'Operator Sekolah',
            'pustakawan' => 'Pustakawan',
            'teknisi' => 'Teknisi Lab/Sarpras',
            'pembimbing_pkl' => 'Pembimbing PKL Sekolah',
            'siswa' => 'Siswa',
            'orang_tua' => 'Orang Tua / Wali',
            'pembimbing_industri' => 'Pembimbing Industri',
        ];

        $roleModels = [];
        foreach ($roles as $name => $label) {
            $roleModels[$name] = Role::firstOrCreate(['name' => $name], [
                'label' => $label,
                'description' => 'Akses role ' . $label,
            ]);
        }

        $permissionsByModule = [
            'Master Data' => [
                'view_student' => 'Lihat Data Siswa',
                'create_student' => 'Tambah Data Siswa',
                'edit_student' => 'Edit Data Siswa',
                'delete_student' => 'Hapus Data Siswa',
                'view_teacher' => 'Lihat Data Guru',
                'create_teacher' => 'Tambah Data Guru',
                'edit_teacher' => 'Edit Data Guru',
                'delete_teacher' => 'Hapus Data Guru',
                'view_class' => 'Lihat Data Kelas',
                'create_class' => 'Tambah Data Kelas',
                'edit_class' => 'Edit Data Kelas',
                'delete_class' => 'Hapus Data Kelas',
                'view_major' => 'Lihat Data Jurusan',
                'create_major' => 'Tambah Data Jurusan',
                'edit_major' => 'Edit Data Jurusan',
                'delete_major' => 'Hapus Data Jurusan',
                'view_room' => 'Lihat Data Ruangan',
                'create_room' => 'Tambah Data Ruangan',
                'edit_room' => 'Edit Data Ruangan',
                'delete_room' => 'Hapus Data Ruangan',
                'view_academic_year' => 'Lihat Tahun Ajaran',
                'manage_academic_year' => 'Kelola Tahun Ajaran & Semester',
            ],
            'Kurikulum Merdeka' => [
                'view_curriculum' => 'Lihat Kurikulum',
                'manage_curriculum' => 'Kelola CP',
                'manage_tp' => 'Kelola Tujuan Pembelajaran (TP)',
                'manage_atp' => 'Kelola ATP Timeline',
                'manage_module' => 'Generate & Kelola Modul Ajar',
            ],
            'Jadwal & Presensi' => [
                'view_schedule' => 'Lihat Jadwal Pelajaran',
                'manage_schedule' => 'Kelola Jadwal',
                'generate_schedule' => 'Generate Jadwal Otomatis',
                'approve_schedule' => 'Approve Jadwal Sekolah',
                'input_attendance' => 'Input Presensi Siswa',
                'view_attendance' => 'Lihat Laporan Presensi',
            ],
            'Penilaian & E-Rapor' => [
                'view_grade' => 'Lihat Nilai',
                'input_grade' => 'Input Nilai Asesmen',
                'publish_grade' => 'Publish Nilai',
                'view_report' => 'Lihat E-Rapor',
                'print_report' => 'Cetak E-Rapor',
            ],
            'CBT Ujian' => [
                'view_cbt' => 'Akses CBT Ujian',
                'manage_cbt' => 'Kelola Ujian CBT',
                'manage_question_bank' => 'Kelola Bank Soal',
            ],
            'BK & Kesiswaan' => [
                'view_bk' => 'Lihat Catatan BK',
                'manage_bk' => 'Kelola Konseling & Pelanggaran BK',
                'view_early_warning' => 'Lihat Early Warning Risk Siswa',
            ],
            'PKL / Prakerin' => [
                'view_pkl' => 'Lihat Data PKL',
                'manage_pkl' => 'Kelola Penempatan PKL',
                'approve_pkl_journal' => 'Approve Jurnal Harian PKL',
            ],
            'Keuangan' => [
                'view_finance' => 'Lihat Laporan Keuangan',
                'manage_finance' => 'Kelola Tagihan & Pembayaran',
            ],
            'Sistem & Keamanan' => [
                'manage_users' => 'Kelola Pengguna System',
                'manage_roles' => 'Kelola Role & Access Rights',
                'view_audit_log' => 'Lihat Audit Log Keamanan',
                'manage_settings' => 'Kelola Pengaturan Sekolah',
            ],
        ];

        $permissionModels = [];
        foreach ($permissionsByModule as $module => $perms) {
            foreach ($perms as $name => $label) {
                $permissionModels[$name] = Permission::firstOrCreate(['name' => $name], [
                    'module' => $module,
                    'label' => $label,
                ]);
            }
        }

        // Attach all permissions to Super Admin & Admin Sekolah
        $allPermissionIds = Permission::pluck('id')->toArray();
        $roleModels['super_admin']->permissions()->sync($allPermissionIds);
        $roleModels['admin_sekolah']->permissions()->sync($allPermissionIds);

        // Kepala Sekolah (View & Reports)
        $kepsekPerms = Permission::whereIn('name', [
            'view_student', 'view_teacher', 'view_class', 'view_major', 'view_room',
            'view_academic_year', 'view_curriculum', 'view_schedule', 'approve_schedule',
            'view_attendance', 'view_grade', 'view_report', 'print_report', 'view_bk',
            'view_early_warning', 'view_pkl', 'view_finance', 'view_audit_log'
        ])->pluck('id')->toArray();
        $roleModels['kepala_sekolah']->permissions()->sync($kepsekPerms);

        // Wakasek Kurikulum
        $kurikulumPerms = Permission::whereIn('name', [
            'view_student', 'view_teacher', 'view_class', 'view_major', 'view_academic_year',
            'view_curriculum', 'manage_curriculum', 'manage_tp', 'manage_atp', 'manage_module',
            'view_schedule', 'manage_schedule', 'generate_schedule', 'approve_schedule',
            'view_grade', 'publish_grade', 'view_report', 'print_report', 'view_cbt', 'manage_cbt'
        ])->pluck('id')->toArray();
        $roleModels['wakasek_kurikulum']->permissions()->sync($kurikulumPerms);

        // Guru
        $guruPerms = Permission::whereIn('name', [
            'view_student', 'view_class', 'view_curriculum', 'manage_tp', 'manage_atp', 'manage_module',
            'view_schedule', 'input_attendance', 'view_attendance', 'view_grade', 'input_grade',
            'view_cbt', 'manage_cbt', 'manage_question_bank'
        ])->pluck('id')->toArray();
        $roleModels['guru']->permissions()->sync($guruPerms);

        // Wali Kelas
        $waliPerms = array_merge($guruPerms, Permission::whereIn('name', [
            'view_report', 'print_report', 'view_bk', 'view_early_warning'
        ])->pluck('id')->toArray());
        $roleModels['wali_kelas']->permissions()->sync($waliPerms);

        // Guru BK
        $bkPerms = Permission::whereIn('name', [
            'view_student', 'view_class', 'view_attendance', 'view_bk', 'manage_bk', 'view_early_warning'
        ])->pluck('id')->toArray();
        $roleModels['guru_bk']->permissions()->sync($bkPerms);

        // Siswa
        $siswaPerms = Permission::whereIn('name', [
            'view_schedule', 'view_attendance', 'view_grade', 'view_report', 'view_cbt', 'view_pkl', 'approve_pkl_journal'
        ])->pluck('id')->toArray();
        $roleModels['siswa']->permissions()->sync($siswaPerms);

        // Orang Tua
        $parentPerms = Permission::whereIn('name', [
            'view_schedule', 'view_attendance', 'view_grade', 'view_report', 'view_finance'
        ])->pluck('id')->toArray();
        $roleModels['orang_tua']->permissions()->sync($parentPerms);
    }
}
