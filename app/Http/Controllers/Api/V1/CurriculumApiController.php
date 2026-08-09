<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LearningOutcome;
use App\Models\Material;
use App\Models\TeachingModule;
use Illuminate\Http\Request;

class CurriculumApiController extends Controller
{
    /**
     * Get Capaian Pembelajaran (CP) & Tujuan Pembelajaran (TP)
     */
    public function learningOutcomes(Request $request)
    {
        $query = LearningOutcome::with(['subject', 'learningObjectives']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('phase')) {
            $query->where('phase', $request->phase);
        }

        $cps = $query->orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $cps,
        ]);
    }

    /**
     * Get Materials / Bahan Ajar
     */
    public function materials(Request $request)
    {
        $query = Material::with(['learningObjective.learningOutcome.subject']);

        $materials = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $materials,
        ]);
    }

    /**
     * Get Modul Ajar (Teaching Modules)
     */
    public function modules(Request $request)
    {
        $query = TeachingModule::with(['subject', 'teacher', 'schoolClass']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $modules = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $modules,
        ]);
    }
}
