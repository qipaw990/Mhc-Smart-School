<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\P5Project;
use App\Models\P5ProjectDimension;
use App\Models\P5StudentScore;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;

class P5ProjectController extends Controller
{
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $projects = P5Project::with(['schoolClass', 'dimensions'])
            ->where('academic_year_id', $ay?->id)
            ->paginate(10);

        return view('p5.index', compact('projects', 'ay'));
    }

    public function create()
    {
        $classes = SchoolClass::all();

        return view('p5.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'theme' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'dimensions' => 'required|array',
            'dimensions.*.name' => 'required|string',
            'dimensions.*.element' => 'required|string',
            'dimensions.*.sub_element' => 'required|string',
        ]);

        $school = School::first();
        $ay = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay->id)->where('is_active', true)->first();

        $project = P5Project::create([
            'school_id' => $school->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $semester?->id,
            'class_id' => $validated['class_id'],
            'theme' => $validated['theme'],
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        foreach ($validated['dimensions'] as $dim) {
            P5ProjectDimension::create([
                'p5_project_id' => $project->id,
                'dimension_name' => $dim['name'],
                'element' => $dim['element'],
                'sub_element' => $dim['sub_element'],
                'target_phase' => 'E',
            ]);
        }

        return redirect()->route('p5.scores', $project->id)
            ->with('success', 'Projek P5 berhasil dibuat! Silakan input lembar penilaian capaian siswa.');
    }

    public function scores(P5Project $project)
    {
        $project->load(['schoolClass.students', 'dimensions.studentScores']);
        $students = $project->schoolClass->students;

        return view('p5.scores', compact('project', 'students'));
    }

    public function storeScores(Request $request, P5Project $project)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
        ]);

        foreach ($validated['scores'] as $dimId => $studentScores) {
            foreach ($studentScores as $studentId => $scoreScale) {
                P5StudentScore::updateOrCreate([
                    'p5_project_dimension_id' => $dimId,
                    'student_id' => $studentId,
                ], [
                    'score' => $scoreScale,
                    'teacher_notes' => 'Tercapai sesuai indikator perkembangan projek.',
                ]);
            }
        }

        return back()->with('success', 'Penilaian Capaian Projek P5 berhasil disimpan!');
    }

    public function printP5(P5Project $project, Student $student)
    {
        $project->load(['schoolClass', 'dimensions.studentScores' => fn($q) => $q->where('student_id', $student->id)]);
        $school = School::first();

        return view('p5.print_p5', compact('project', 'student', 'school'));
    }

    public function destroy(P5Project $project)
    {
        $project->delete();
        return redirect()->route('p5.index')->with('success', 'Projek P5 berhasil dihapus.');
    }
}
