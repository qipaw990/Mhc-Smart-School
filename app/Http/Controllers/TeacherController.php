<?php

namespace App\Http\Controllers;

use App\Models\Role;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $teachers = $query->paginate(15);
        return view('master.teachers', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nuptk' => 'nullable|string|max:30|unique:teachers,nuptk',
            'nip' => 'nullable|string|max:30',
            'name' => 'required|string|max:255',
            'title_prefix' => 'nullable|string|max:30',
            'title_suffix' => 'nullable|string|max:30',
            'gender' => 'required|in:L,P',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'employment_status' => 'required|string',
            'position' => 'nullable|string',
            'create_user_account' => 'nullable|boolean',
        ]);

        $school = School::first();

        DB::transaction(function () use ($school, $validated, $request) {
            $user = null;
            if ($request->boolean('create_user_account', true)) {
                $username = $validated['nuptk'] ?: ($validated['nip'] ?: strtolower(explode(' ', $validated['name'])[0]) . rand(100, 999));
                $user = User::create([
                    'school_id' => $school->id,
                    'name' => $validated['name'] . ($validated['title_suffix'] ? ', ' . $validated['title_suffix'] : ''),
                    'username' => $username,
                    'email' => $validated['email'],
                    'password' => bcrypt('password'),
                    'status' => 'active',
                ]);

                $guruRole = Role::where('name', 'guru')->first();
                if ($guruRole) {
                    $user->roles()->attach($guruRole->id);
                }
            }

            Teacher::create([
                'school_id' => $school->id,
                'user_id' => $user?->id,
                'nuptk' => $validated['nuptk'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'name' => $validated['name'],
                'title_prefix' => $validated['title_prefix'],
                'title_suffix' => $validated['title_suffix'],
                'gender' => $validated['gender'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'employment_status' => $validated['employment_status'],
                'position' => $validated['position'],
            ]);
        });

        return back()->with('success', 'Data Guru berhasil ditambahkan beserta akun login (NUPTK)!');
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'nuptk' => 'nullable|string|max:30|unique:teachers,nuptk,' . $teacher->id,
            'nip' => 'nullable|string|max:30',
            'name' => 'required|string|max:255',
            'title_prefix' => 'nullable|string|max:30',
            'title_suffix' => 'nullable|string|max:30',
            'gender' => 'required|in:L,P',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:30',
            'employment_status' => 'required|string',
            'position' => 'nullable|string',
        ]);

        $teacher->update($validated);
        if ($teacher->user) {
            $teacher->user->update([
                'name' => $validated['name'] . ($validated['title_suffix'] ? ', ' . $validated['title_suffix'] : ''),
                'email' => $validated['email'],
            ]);
        }

        return back()->with('success', 'Data Guru ' . $teacher->name . ' berhasil diperbarui!');
    }

    public function destroy(Teacher $teacher)
    {
        if ($teacher->user) {
            $teacher->user->delete();
        }
        $teacher->delete();

        return back()->with('success', 'Data Guru berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return ExcelService::downloadStyledExcel(
            'template_import_guru.xls',
            'TEMPLATE IMPORT DATA GURU & TENDIK',
            ['nama', 'nuptk', 'nip', 'jenis_kelamin', 'email', 'telepon', 'status_kepegawaian', 'jabatan'],
            [
                ['Budi Santoso S.Pd.', '1234567890123456', '198501152010011001', 'L', 'budi@smk.sch.id', '081234567890', 'PNS', 'Guru Mata Pelajaran'],
                ['Siti Aminah M.Pd.', '9876543210987654', '199002202015022002', 'P', 'siti@smk.sch.id', '085712345678', 'PPPK', 'Wali Kelas'],
            ],
            [
                'Isi data sesuai kolom yang tersedia.',
                'Jenis Kelamin diisi: L (Laki-laki) atau P (Perempuan).',
                'Status Kepegawaian diisi: PNS, PPPK, GTY, atau GTT.',
                'Sistem akan otomatis membuat akun login (Username = NUPTK/NIP, Password = password).'
            ]
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $school = School::first();
        $guruRole = Role::where('name', 'guru')->first();
        $importedCount = 0;
        $skippedCount = 0;

        $rows = ExcelService::parseUploadedFile($request->file('file'));

        foreach ($rows as $data) {
            $name = trim($data[0] ?? '');
            if (empty($name) || strtolower($name) === 'nama' || str_contains(strtolower($name), 'template')) continue;

            $nuptk = trim($data[1] ?? '') ?: null;
            $nip = trim($data[2] ?? '') ?: null;
            $gender = strtoupper(trim($data[3] ?? 'L'));
            if (!in_array($gender, ['L', 'P'])) $gender = 'L';
            $email = trim($data[4] ?? '') ?: (strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@smk.sch.id');
            $phone = trim($data[5] ?? '') ?: null;
            $employmentStatus = trim($data[6] ?? 'GTY') ?: 'GTY';
            $position = trim($data[7] ?? 'Guru Mata Pelajaran') ?: 'Guru Mata Pelajaran';

            if (Teacher::where('email', $email)->orWhere(function($q) use ($nuptk) {
                if ($nuptk) $q->where('nuptk', $nuptk);
            })->exists()) {
                $skippedCount++;
                continue;
            }

            DB::transaction(function () use ($school, $guruRole, $name, $nuptk, $nip, $gender, $email, $phone, $employmentStatus, $position) {
                $username = $nuptk ?: ($nip ?: strtolower(explode(' ', $name)[0]) . rand(100, 999));
                
                $user = User::create([
                    'school_id' => $school->id,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'status' => 'active',
                ]);

                if ($guruRole) {
                    $user->roles()->attach($guruRole->id);
                }

                Teacher::create([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                    'nuptk' => $nuptk,
                    'nip' => $nip,
                    'name' => $name,
                    'gender' => $gender,
                    'email' => $email,
                    'phone' => $phone,
                    'employment_status' => $employmentStatus,
                    'position' => $position,
                ]);
            });

            $importedCount++;
        }

        return back()->with('success', "Import Excel Guru berhasil! {$importedCount} data ditambahkan, {$skippedCount} data dilewati (duplikat).");
    }
}
