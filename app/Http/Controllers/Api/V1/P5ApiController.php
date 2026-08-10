<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\P5Project;
use App\Models\P5ProjectDimension;
use App\Models\P5StudentScore;
use App\Models\School;
use App\Models\Semester;
use Illuminate\Http\Request;

class P5ApiController extends Controller
{
    /**
     * List P5 Projects
     */
    public function index(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();

        $query = P5Project::with(['schoolClass', 'dimensions'])
            ->where('academic_year_id', $ay?->id);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data'   => $projects,
        ]);
    }

    /**
     * Store new P5 Project
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id'                 => 'required|exists:classes,id',
            'theme'                    => 'required|string|max:255',
            'title'                    => 'required|string|max:255',
            'description'              => 'required|string',
            'dimensions'               => 'required|array',
            'dimensions.*.name'        => 'required|string',
            'dimensions.*.element'     => 'required|string',
            'dimensions.*.sub_element' => 'required|string',
        ]);

        $school   = School::first();
        $ay       = AcademicYear::where('is_active', true)->first();
        $semester = Semester::where('academic_year_id', $ay?->id)->where('is_active', true)->first();

        $project = P5Project::create([
            'school_id'        => $school?->id,
            'academic_year_id' => $ay?->id,
            'semester_id'      => $semester?->id,
            'class_id'         => $validated['class_id'],
            'theme'            => $validated['theme'],
            'title'            => $validated['title'],
            'description'      => $validated['description'],
        ]);

        foreach ($validated['dimensions'] as $dim) {
            P5ProjectDimension::create([
                'p5_project_id'  => $project->id,
                'dimension_name' => $dim['name'],
                'element'        => $dim['element'],
                'sub_element'    => $dim['sub_element'],
                'target_phase'   => 'E',
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Projek P5 berhasil dibuat!',
            'data'    => $project->load('dimensions'),
        ], 201);
    }

    /**
     * Get Scores for P5 Project
     */
    public function scores(P5Project $project)
    {
        $project->load(['schoolClass.students', 'dimensions.studentScores']);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'project'  => $project,
                'students' => $project->schoolClass?->students ?? [],
            ],
        ]);
    }

    /**
     * Store Scores for P5 Project
     */
    public function storeScores(Request $request, P5Project $project)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
        ]);

        foreach ($validated['scores'] as $dimId => $studentScores) {
            foreach ($studentScores as $studentId => $scoreScale) {
                P5StudentScore::updateOrCreate([
                    'p5_project_dimension_id' => $dimId,
                    'student_id'              => $studentId,
                ], [
                    'score'         => $scoreScale,
                    'teacher_notes' => 'Tercapai sesuai indikator perkembangan projek.',
                ]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Penilaian Capaian Projek P5 berhasil disimpan!',
        ]);
    }

    /**
     * Delete P5 Project
     */
    public function destroy(P5Project $project)
    {
        $project->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Projek P5 berhasil dihapus.',
        ]);
    }
}
