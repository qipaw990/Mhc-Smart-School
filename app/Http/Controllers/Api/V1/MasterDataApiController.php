<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class MasterDataApiController extends Controller
{
    /**
     * List all classes
     */
    public function classes()
    {
        $classes = SchoolClass::with(['major', 'homeroomTeacher'])
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $classes,
        ]);
    }

    /**
     * List / Filter Students
     */
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

    /**
     * List / Filter Teachers
     */
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

    /**
     * List Subjects
     */
    public function subjects()
    {
        $subjects = Subject::orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $subjects,
        ]);
    }

    /**
     * List Majors
     */
    public function majors()
    {
        $majors = Major::orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $majors,
        ]);
    }
}
