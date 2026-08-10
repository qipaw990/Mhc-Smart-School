<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\LearningObjective;
use App\Models\LearningOutcome;
use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\Material;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeachingModule;
use Illuminate\Http\Request;

class CurriculumApiController extends Controller
{
    // ── 1. LEARNING OUTCOMES (CP & TP) ──────────────────────────────────────

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

    public function storeCp(Request $request)
    {
        $validated = $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'phase'       => 'required|in:E,F',
            'code'        => 'required|string|max:30',
            'element'     => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ay = AcademicYear::where('is_active', true)->first();

        $cp = LearningOutcome::create(array_merge($validated, [
            'academic_year_id' => $ay?->id,
            'status'           => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Capaian Pembelajaran (CP) baru berhasil ditambahkan!',
            'data'    => $cp,
        ], 201);
    }

    public function updateCp(Request $request, LearningOutcome $learningOutcome)
    {
        $validated = $request->validate([
            'phase'       => 'required|in:E,F',
            'code'        => 'required|string|max:30',
            'element'     => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $learningOutcome->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Capaian Pembelajaran ' . $learningOutcome->code . ' berhasil diperbarui!',
            'data'    => $learningOutcome,
        ]);
    }

    public function destroyCp(LearningOutcome $learningOutcome)
    {
        $learningOutcome->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Capaian Pembelajaran berhasil dihapus.',
        ]);
    }

    public function storeTp(Request $request)
    {
        $validated = $request->validate([
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'code'                => 'required|string|max:30',
            'order_number'        => 'required|integer|min:1',
            'description'         => 'required|string',
            'semester_number'     => 'required|in:1,2',
            'estimated_hours'     => 'required|integer|min:1',
        ]);

        $tp = LearningObjective::create(array_merge($validated, [
            'status' => 'active',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Tujuan Pembelajaran (TP) berhasil ditambahkan!',
            'data'    => $tp,
        ], 201);
    }

    public function updateTp(Request $request, LearningObjective $learningObjective)
    {
        $validated = $request->validate([
            'code'            => 'required|string|max:30',
            'order_number'    => 'required|integer|min:1',
            'description'     => 'required|string',
            'semester_number' => 'required|in:1,2',
            'estimated_hours' => 'required|integer|min:1',
        ]);

        $learningObjective->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tujuan Pembelajaran ' . $learningObjective->code . ' berhasil diperbarui!',
            'data'    => $learningObjective,
        ]);
    }

    public function destroyTp(LearningObjective $learningObjective)
    {
        $learningObjective->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Tujuan Pembelajaran berhasil dihapus.',
        ]);
    }

    // ── 2. ALUR TUJUAN PEMBELAJARAN (ATP) ───────────────────────────────────

    public function atpIndex(Request $request)
    {
        $ay = AcademicYear::where('is_active', true)->first();
        $subjectId = $request->input('subject_id');

        $query = LearningPath::with(['items.learningObjective.learningOutcome'])
            ->where('academic_year_id', $ay?->id);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $paths = $query->get();

        return response()->json([
            'status' => 'success',
            'data'   => $paths,
        ]);
    }

    public function storeAtpHeader(Request $request)
    {
        $validated = $request->validate([
            'subject_id'      => 'required|exists:subjects,id',
            'phase'           => 'required|in:E,F',
            'semester_number' => 'required|in:1,2',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
        ]);

        $ay      = AcademicYear::where('is_active', true)->first();
        $subject = Subject::findOrFail($validated['subject_id']);

        $atp = LearningPath::updateOrCreate([
            'subject_id'       => $subject->id,
            'academic_year_id' => $ay?->id,
            'semester_number'  => $validated['semester_number'],
        ], [
            'major_id'    => $subject->major_id,
            'phase'       => $validated['phase'],
            'title'       => $validated['title'],
            'description' => $validated['description'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kerangka ATP berhasil disimpan!',
            'data'    => $atp,
        ], 201);
    }

    public function addAtpItem(Request $request)
    {
        $validated = $request->validate([
            'learning_path_id'      => 'required|exists:learning_paths,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'week_number'           => 'required|integer|min:1|max:24',
            'hour_allocation'       => 'required|integer|min:1',
            'topic'                 => 'required|string|max:255',
            'assessment_plan'       => 'nullable|string',
        ]);

        $maxSeq = LearningPathItem::where('learning_path_id', $validated['learning_path_id'])->max('sequence_order') ?? 0;

        $item = LearningPathItem::create(array_merge($validated, [
            'sequence_order' => $maxSeq + 1,
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Item ATP berhasil ditambahkan!',
            'data'    => $item,
        ], 201);
    }

    public function deleteAtpItem(LearningPathItem $item)
    {
        $item->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Item ATP berhasil dihapus.',
        ]);
    }

    // ── 3. MATERIALS (BAHAN AJAR) ───────────────────────────────────────────

    public function materials(Request $request)
    {
        $query = Material::with(['learningObjective.learningOutcome.subject']);

        $materials = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data'   => $materials,
        ]);
    }

    public function storeMaterial(Request $request)
    {
        $validated = $request->validate([
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'title'                 => 'required|string|max:255',
            'type'                  => 'required|in:pdf,video,document,slide,link',
            'content'               => 'nullable|string',
            'file_url'              => 'nullable|string',
        ]);

        $school = School::first();

        $material = Material::create(array_merge($validated, [
            'school_id' => $school?->id,
            'status'    => 'published',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Bahan Ajar berhasil ditambahkan!',
            'data'    => $material,
        ], 201);
    }

    public function updateMaterial(Request $request, Material $material)
    {
        $validated = $request->validate([
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'title'                 => 'required|string|max:255',
            'type'                  => 'required|in:pdf,video,document,slide,link',
            'content'               => 'nullable|string',
            'file_url'              => 'nullable|string',
        ]);

        $material->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Bahan Ajar berhasil diperbarui!',
            'data'    => $material,
        ]);
    }

    public function destroyMaterial(Material $material)
    {
        $material->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Bahan Ajar berhasil dihapus.',
        ]);
    }

    // ── 4. TEACHING MODULES (MODUL AJAR) ────────────────────────────────────

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

    public function storeModule(Request $request)
    {
        $validated = $request->validate([
            'subject_id'        => 'required|exists:subjects,id',
            'class_id'          => 'required|exists:classes,id',
            'title'             => 'required|string|max:255',
            'target_phase'      => 'required|in:E,F',
            'time_allocation'   => 'required|string|max:100',
            'model_pembelajaran' => 'required|string|max:100',
            'profil_pancasila'  => 'nullable|array',
            'komponen_inti'     => 'required|array',
        ]);

        $school    = School::first();
        $ay        = AcademicYear::where('is_active', true)->first();
        $teacherId = $request->user()->teacher?->id;

        $module = TeachingModule::create(array_merge($validated, [
            'school_id'        => $school?->id,
            'academic_year_id' => $ay?->id,
            'teacher_id'       => $teacherId,
            'status'           => 'published',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Modul Ajar berhasil disimpan!',
            'data'    => $module,
        ], 201);
    }

    public function destroyModule(TeachingModule $module)
    {
        $module->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Modul Ajar berhasil dihapus.',
        ]);
    }
}
