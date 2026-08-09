<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\School;
use App\Models\Student;
use App\Models\TeachingJournal;
use Illuminate\Http\Request;

class JournalApiController extends Controller
{
    /**
     * List Teaching Journals
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = TeachingJournal::with(['teacher', 'subject', 'schoolClass']);

        // Scoped for teacher
        if ($user->teacher) {
            $query->where('teacher_id', $user->teacher->id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        $journals = $query->orderBy('date', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data'   => $journals,
        ]);
    }

    /**
     * Create KBM Journal from Mobile App
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'            => 'required|date',
            'class_id'        => 'required|exists:classes,id',
            'subject_id'      => 'required|exists:subjects,id',
            'teaching_hour'   => 'required|string|max:50',
            'materi_pokok'    => 'required|string',
            'kegiatan'        => 'nullable|string',
            'catatan'         => 'nullable|string',
            'absent_students' => 'nullable|array', // Array of student_ids who are absent/sick/permit
            'absent_students.*.id'     => 'required_with:absent_students|exists:students,id',
            'absent_students.*.status' => 'required_with:absent_students|in:S,I,A,T',
        ], [
            'date.required'          => 'Tanggal KBM wajib diisi.',
            'class_id.required'      => 'Kelas wajib dipilih.',
            'subject_id.required'    => 'Mata pelajaran wajib dipilih.',
            'materi_pokok.required'  => 'Materi pokok / bahasan wajib diisi.',
        ]);

        $user      = $request->user();
        $school    = School::first();
        $ay        = AcademicYear::where('is_active', true)->first();
        $teacherId = $user->teacher?->id ?? $request->input('teacher_id');

        if (!$teacherId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya pengguna dengan profil guru yang dapat mengisi Jurnal KBM.',
            ], 403);
        }

        $journal = TeachingJournal::create([
            'school_id'        => $school->id,
            'academic_year_id' => $ay->id,
            'teacher_id'       => $teacherId,
            'class_id'         => $validated['class_id'],
            'subject_id'       => $validated['subject_id'],
            'date'             => $validated['date'],
            'teaching_hour'    => $validated['teaching_hour'],
            'materi_pokok'     => $validated['materi_pokok'],
            'kegiatan'         => $validated['kegiatan'] ?? null,
            'catatan'          => $validated['catatan'] ?? null,
        ]);

        // Auto-record student attendances if absent_students payload is provided
        if (!empty($validated['absent_students'])) {
            foreach ($validated['absent_students'] as $item) {
                Attendance::updateOrCreate([
                    'date'       => $validated['date'],
                    'student_id' => $item['id'],
                ], [
                    'school_id'        => $school->id,
                    'academic_year_id' => $ay->id,
                    'teacher_id'       => $teacherId,
                    'time'             => now()->toTimeString(),
                    'type'             => 'subject_session',
                    'method'           => 'journal_entry',
                    'status'           => $item['status'],
                ]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Jurnal Mengajar KBM berhasil disimpan!',
            'data'    => $journal->load(['subject', 'schoolClass']),
        ], 201);
    }
}
