<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Room;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataApiController extends Controller
{
    // ── 1. CLASSES (KELAS) ───────────────────────────────────────────────────

    public function classes(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::with(['major', 'room', 'homeroomTeacher'])
            ->withCount('students')
            ->where('academic_year_id', $ay?->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $classes,
        ]);
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'grade_level'         => 'required|in:X,XI,XII',
            'major_id'            => 'required|exists:majors,id',
            'room_id'             => 'nullable|exists:rooms,id',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
            'capacity'            => 'required|integer|min:1',
        ]);

        $school = School::first();
        $ay     = AcademicYear::where('is_active', true)->first();

        $class = SchoolClass::create(array_merge($validated, [
            'school_id'        => $school?->id,
            'academic_year_id' => $ay?->id,
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Kelas ' . $validated['name'] . ' berhasil ditambahkan!',
            'data'    => $class,
        ], 201);
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:100',
            'grade_level'         => 'required|in:X,XI,XII',
            'major_id'            => 'required|exists:majors,id',
            'room_id'             => 'nullable|exists:rooms,id',
            'homeroom_teacher_id' => 'nullable|exists:teachers,id',
            'capacity'            => 'required|integer|min:1',
        ]);

        $class->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data Kelas ' . $class->name . ' berhasil diperbarui!',
            'data'    => $class,
        ]);
    }

    public function destroyClass(SchoolClass $class)
    {
        $class->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Kelas berhasil dihapus.',
        ]);
    }

    // ── 2. STUDENTS (SISWA) ──────────────────────────────────────────────────

    public function students(Request $request)
    {
        $query = Student::with(['currentClass', 'major', 'user']);

        if ($request->filled('class_id')) {
            $query->where('current_class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $students,
        ]);
    }

    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'nisn'             => 'required|string|max:20|unique:students,nisn',
            'nis'              => 'required|string|max:20|unique:students,nis',
            'gender'           => 'required|in:L,P',
            'current_class_id' => 'required|exists:classes,id',
            'phone'            => 'nullable|string|max:20',
            'parent_phone'     => 'nullable|string|max:20',
            'address'          => 'nullable|string',
        ]);

        $school = School::first();
        $class  = SchoolClass::find($validated['current_class_id']);

        $student = Student::create(array_merge($validated, [
            'school_id' => $school?->id,
            'major_id'  => $class?->major_id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Siswa ' . $validated['name'] . ' berhasil ditambahkan!',
            'data'    => $student,
        ], 201);
    }

    public function updateStudent(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'nisn'             => 'required|string|max:20|unique:students,nisn,' . $student->id,
            'nis'              => 'required|string|max:20|unique:students,nis,' . $student->id,
            'gender'           => 'required|in:L,P',
            'current_class_id' => 'required|exists:classes,id',
            'phone'            => 'nullable|string|max:20',
            'parent_phone'     => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'status'           => 'required|in:active,graduated,moved,dropped_out',
        ]);

        $class = SchoolClass::find($validated['current_class_id']);
        $validated['major_id'] = $class?->major_id;

        $student->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data siswa ' . $student->name . ' berhasil diperbarui!',
            'data'    => $student,
        ]);
    }

    public function destroyStudent(Student $student)
    {
        $student->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Data siswa berhasil dihapus.',
        ]);
    }

    // ── 3. TEACHERS (GURU) ───────────────────────────────────────────────────

    public function teachers(Request $request)
    {
        $query = Teacher::with(['user', 'homeroomClasses']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $teachers,
        ]);
    }

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'nip'    => 'nullable|string|max:30|unique:teachers,nip',
            'nuptk'  => 'nullable|string|max:30|unique:teachers,nuptk',
            'gender' => 'required|in:L,P',
            'phone'  => 'nullable|string|max:20',
            'email'  => 'nullable|email|max:255',
        ]);

        $school = School::first();

        $teacher = Teacher::create(array_merge($validated, [
            'school_id' => $school?->id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Guru ' . $validated['name'] . ' berhasil ditambahkan!',
            'data'    => $teacher,
        ], 201);
    }

    public function updateTeacher(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'nip'    => 'nullable|string|max:30|unique:teachers,nip,' . $teacher->id,
            'nuptk'  => 'nullable|string|max:30|unique:teachers,nuptk,' . $teacher->id,
            'gender' => 'required|in:L,P',
            'phone'  => 'nullable|string|max:20',
            'email'  => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $teacher->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data guru ' . $teacher->name . ' berhasil diperbarui!',
            'data'    => $teacher,
        ]);
    }

    public function destroyTeacher(Teacher $teacher)
    {
        $teacher->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Data guru berhasil dihapus.',
        ]);
    }

    // ── 4. SUBJECTS (MAPEL) ──────────────────────────────────────────────────

    public function subjects()
    {
        $subjects = Subject::orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $subjects,
        ]);
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:30|unique:subjects,code',
            'name'           => 'required|string|max:255',
            'group'          => 'required|in:A_general,B_vocational,C_concentration,mulok,p5',
            'phase'          => 'required|in:E,F',
            'type'           => 'required|in:theory,practice,theory_practice',
            'hours_per_week' => 'required|integer|min:1',
            'total_hours'    => 'required|integer|min:1',
            'major_id'       => 'nullable|exists:majors,id',
        ]);

        $school = School::first();

        $subject = Subject::create(array_merge($validated, [
            'school_id' => $school?->id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Mata pelajaran ' . $validated['name'] . ' berhasil ditambahkan!',
            'data'    => $subject,
        ], 201);
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:30|unique:subjects,code,' . $subject->id,
            'name'           => 'required|string|max:255',
            'group'          => 'required|in:A_general,B_vocational,C_concentration,mulok,p5',
            'phase'          => 'required|in:E,F',
            'type'           => 'required|in:theory,practice,theory_practice',
            'hours_per_week' => 'required|integer|min:1',
            'total_hours'    => 'required|integer|min:1',
            'major_id'       => 'nullable|exists:majors,id',
            'status'         => 'required|in:active,inactive',
        ]);

        $subject->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Mata pelajaran ' . $subject->name . ' berhasil diperbarui!',
            'data'    => $subject,
        ]);
    }

    public function destroySubject(Subject $subject)
    {
        $subject->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Mata pelajaran berhasil dihapus.',
        ]);
    }

    // ── 5. MAJORS (JURUSAN) ──────────────────────────────────────────────────

    public function majors()
    {
        $majors = Major::withCount(['classes', 'students'])->orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $majors,
        ]);
    }

    public function storeMajor(Request $request)
    {
        $validated = $request->validate([
            'code'          => 'required|string|max:20|unique:majors,code',
            'name'          => 'required|string|max:255',
            'head_of_major' => 'nullable|string|max:255',
            'description'   => 'nullable|string',
        ]);

        $school = School::first();

        $major = Major::create(array_merge($validated, [
            'school_id' => $school?->id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurusan ' . $validated['name'] . ' berhasil ditambahkan!',
            'data'    => $major,
        ], 201);
    }

    public function updateMajor(Request $request, Major $major)
    {
        $validated = $request->validate([
            'code'          => 'required|string|max:20|unique:majors,code,' . $major->id,
            'name'          => 'required|string|max:255',
            'head_of_major' => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'required|in:active,inactive',
        ]);

        $major->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data jurusan ' . $major->code . ' berhasil diperbarui!',
            'data'    => $major,
        ]);
    }

    public function destroyMajor(Major $major)
    {
        $major->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Jurusan berhasil dihapus.',
        ]);
    }

    // ── 6. ROOMS (RUANGAN) ───────────────────────────────────────────────────

    public function rooms()
    {
        $rooms = Room::orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $rooms,
        ]);
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'code'     => 'required|string|max:30|unique:rooms,code',
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:classroom,lab,workshop,hall,library',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
        ]);

        $school = School::first();

        $room = Room::create(array_merge($validated, [
            'school_id' => $school?->id,
            'status'    => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Ruangan ' . $validated['name'] . ' berhasil ditambahkan!',
            'data'    => $room,
        ], 201);
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'code'     => 'required|string|max:30|unique:rooms,code,' . $room->id,
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:classroom,lab,workshop,hall,library',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'status'   => 'required|in:active,maintenance',
        ]);

        $room->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Ruangan ' . $room->code . ' berhasil diperbarui!',
            'data'    => $room,
        ]);
    }

    public function destroyRoom(Room $room)
    {
        $room->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Ruangan berhasil dihapus.',
        ]);
    }

    // ── 7. ACADEMIC YEARS (TAHUN AJARAN) ─────────────────────────────────────

    public function academicYears()
    {
        $academicYears = AcademicYear::with('semesters')->orderBy('start_date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $academicYears,
        ]);
    }

    public function storeAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $school = School::first();

        $ay = DB::transaction(function () use ($school, $validated) {
            $ay = AcademicYear::create([
                'school_id'  => $school?->id,
                'name'       => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date'   => $validated['end_date'],
                'is_active'  => false,
            ]);

            Semester::create([
                'academic_year_id' => $ay->id,
                'name'             => 'Ganjil',
                'semester_number'  => 1,
                'is_active'        => true,
            ]);

            Semester::create([
                'academic_year_id' => $ay->id,
                'name'             => 'Genap',
                'semester_number'  => 2,
                'is_active'        => false,
            ]);

            return $ay;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Tahun Ajaran baru berhasil dibuat!',
            'data'    => $ay->load('semesters'),
        ], 201);
    }

    public function setActiveAcademicYear(AcademicYear $academicYear)
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Tahun Ajaran ' . $academicYear->name . ' berhasil diaktifkan!',
        ]);
    }

    public function setActiveSemester(Semester $semester)
    {
        DB::transaction(function () use ($semester) {
            Semester::where('academic_year_id', $semester->academic_year_id)->update(['is_active' => false]);
            $semester->update(['is_active' => true]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Semester ' . $semester->name . ' berhasil diaktifkan!',
        ]);
    }
}
