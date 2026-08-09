<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Major;

use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentHistory;
use App\Models\StudentParent;
use App\Models\User;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['currentClass', 'major', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('current_class_id', $request->class_id);
        }

        if ($request->filled('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->paginate(15);
        $classes = SchoolClass::all();
        $majors = Major::all();

        return view('master.students', compact('students', 'classes', 'majors'));
    }

    public function show(Student $student)
    {
        $student->load(['currentClass', 'major', 'user', 'parents', 'histories.schoolClass', 'histories.academicYear']);
        return view('master.student_detail', compact('student'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nis' => 'nullable|string|max:20|unique:students,nis',
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'nik' => 'nullable|string|max:30',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'current_class_id' => 'required|exists:classes,id',
            'major_id' => 'required|exists:majors,id',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:30',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:30',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $siswaRole = Role::where('name', 'siswa')->first();

        DB::transaction(function () use ($school, $ay, $siswaRole, $validated) {
            // Create user account for student
            $user = User::create([
                'school_id' => $school->id,
                'name' => $validated['name'],
                'username' => $validated['nisn'],
                'email' => $validated['email'] ?: strtolower(str_replace(' ', '', $validated['name'])) . '@siswa.mhc.sch.id',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]);
            if ($siswaRole) {
                $user->roles()->attach($siswaRole->id);
            }

            // Create student
            $student = Student::create([
                'school_id' => $school->id,
                'user_id' => $user->id,
                'current_class_id' => $validated['current_class_id'],
                'major_id' => $validated['major_id'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'],
                'nik' => $validated['nik'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'religion' => $validated['religion'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'parent_name' => $validated['parent_name'] ?? null,
                'parent_phone' => $validated['parent_phone'] ?? null,
                'email' => $user->email,
                'entry_year' => date('Y'),
                'status' => 'active',
            ]);

            // Parent details
            if (!empty($validated['father_name']) || !empty($validated['mother_name'])) {
                $parent = StudentParent::create([
                    'father_name' => $validated['father_name'],
                    'father_phone' => $validated['father_phone'],
                    'mother_name' => $validated['mother_name'],
                    'mother_phone' => $validated['mother_phone'],
                    'address' => $validated['address'],
                ]);
                $student->parents()->attach($parent->id, ['relationship' => 'father']);
            }

            // Class history
            StudentHistory::create([
                'student_id' => $student->id,
                'academic_year_id' => $ay->id,
                'class_id' => $validated['current_class_id'],
                'action' => 'enrolled',
                'notes' => 'Pendaftaran Siswa Baru',
            ]);
        });

        return back()->with('success', 'Data Siswa baru berhasil ditambahkan!');
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nis' => 'nullable|string|max:20|unique:students,nis,' . $student->id,
            'nisn' => 'required|string|max:20|unique:students,nisn,' . $student->id,
            'nik' => 'nullable|string|max:30',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'religion' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'current_class_id' => 'required|exists:classes,id',
            'major_id' => 'required|exists:majors,id',
            'status' => 'required|in:active,graduated,transferred,dropped_out,deceased',
        ]);

        $student->update($validated);
        if ($student->user) {
            $student->user->update([
                'name' => $validated['name'],
                'username' => $validated['nisn'],
            ]);
        }

        return back()->with('success', 'Data Siswa ' . $student->name . ' berhasil diperbarui!');
    }

    public function destroy(Student $student)
    {
        if ($student->user) {
            $student->user->delete();
        }
        $student->delete();

        return back()->with('success', 'Data Siswa berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return ExcelService::downloadStyledExcel(
            'template_import_siswa.xls',
            'TEMPLATE IMPORT DATA SISWA',
            ['nama', 'nisn', 'nis', 'jenis_kelamin', 'kelas', 'jurusan_kode', 'tempat_lahir', 'tanggal_lahir', 'agama', 'telepon', 'email'],
            [
                ['Ahmad Pratama', '0061234599', '22231001', 'L', 'X TKM 1', 'TKM', 'Jakarta', '2006-05-14', 'Islam', '085712345678', 'ahmad@siswa.smk.sch.id'],
                ['Dewi Lestari', '0061234598', '22231002', 'P', 'X TBSM 1', 'TBSM', 'Bandung', '2006-08-20', 'Islam', '081298765432', 'dewi@siswa.smk.sch.id'],
            ],
            [
                'Isi data sesuai kolom yang tersedia.',
                'Jenis Kelamin diisi: L (Laki-laki) atau P (Perempuan).',
                'Nama Kelas disesuaikan dengan daftar Kelas yang ada di sistem (contoh: X TKM 1).',
                'Kode Jurusan disesuaikan dengan daftar Jurusan di sistem (contoh: TKM, TBSM).',
                'Format Tanggal Lahir: YYYY-MM-DD (Contoh: 2006-05-14).',
                'Sistem akan otomatis membuat akun login portal siswa (Username = NISN, Password = password).'
            ]
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $school = School::first();
        $studentRole = Role::where('name', 'siswa')->first();
        $classesMap = SchoolClass::pluck('id', 'name')->toArray();
        $majorsMap = Major::pluck('id', 'code')->toArray();
        $defaultClassId = !empty($classesMap) ? reset($classesMap) : null;
        $defaultMajorId = !empty($majorsMap) ? reset($majorsMap) : null;

        $importedCount = 0;
        $skippedCount = 0;

        $rows = ExcelService::parseUploadedFile($request->file('file'));

        foreach ($rows as $data) {
            $name = trim($data[0] ?? '');
            if (empty($name) || strtolower($name) === 'nama' || str_contains(strtolower($name), 'template')) continue;

            $nisn = trim($data[1] ?? '') ?: null;
            $nis = trim($data[2] ?? '') ?: null;
            $gender = strtoupper(trim($data[3] ?? 'L'));
            if (!in_array($gender, ['L', 'P'])) $gender = 'L';
            
            $className = trim($data[4] ?? '');
            $majorCode = trim($data[5] ?? '');
            $birthPlace = trim($data[6] ?? '') ?: null;
            $birthDate = trim($data[7] ?? '') ?: null;
            $religion = trim($data[8] ?? 'Islam') ?: 'Islam';
            $phone = trim($data[9] ?? '') ?: null;
            $email = trim($data[10] ?? '') ?: (strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@siswa.smk.sch.id');

            $classId = $classesMap[$className] ?? $defaultClassId;
            $majorId = $majorsMap[$majorCode] ?? $defaultMajorId;

            if (Student::where('nisn', $nisn)->orWhere('nis', $nis)->exists()) {
                $skippedCount++;
                continue;
            }

            DB::transaction(function () use ($school, $studentRole, $name, $nisn, $nis, $gender, $classId, $majorId, $birthPlace, $birthDate, $religion, $phone, $email) {
                $username = $nisn ?: ($nis ?: strtolower(explode(' ', $name)[0]) . rand(100, 999));
                
                $user = User::create([
                    'school_id' => $school->id,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'status' => 'active',
                ]);

                if ($studentRole) {
                    $user->roles()->attach($studentRole->id);
                }

                Student::create([
                    'school_id' => $school->id,
                    'user_id' => $user->id,
                    'nisn' => $nisn,
                    'nis' => $nis,
                    'name' => $name,
                    'gender' => $gender,
                    'current_class_id' => $classId,
                    'major_id' => $majorId,
                    'birth_place' => $birthPlace,
                    'birth_date' => $birthDate,
                    'religion' => $religion,
                    'phone' => $phone,
                    'email' => $email,
                    'entry_year' => date('Y'),
                    'status' => 'active',
                ]);
            });

            $importedCount++;
        }

        return back()->with('success', "Import Excel Siswa berhasil! {$importedCount} data ditambahkan, {$skippedCount} data dilewati (duplikat).");
    }

    /**
     * Cetak kartu pelajar 1 siswa
     */
    public function printIdCard(Student $student)
    {
        $school  = School::first();
        $student->load(['currentClass', 'major', 'user']);

        return view('master.student_id_card', compact('student', 'school'));
    }

    /**
     * Cetak kartu pelajar massal per kelas
     */
    public function printIdCards(Request $request)
    {
        $school  = School::first();
        $classId = $request->get('class_id');

        $query = Student::with(['currentClass', 'major', 'user'])
            ->where('status', 'active');

        if ($classId) {
            $query->where('current_class_id', $classId);
        }

        $students = $query->orderBy('name')->get();
        $classes  = SchoolClass::orderBy('name')->get();

        return view('master.student_id_cards_bulk', compact('students', 'school', 'classes', 'classId'));
    }
}

