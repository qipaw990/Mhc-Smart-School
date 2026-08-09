<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'class_id',
        'learning_outcome_id',
        'learning_objective_id',
        'title',
        'phase',
        'grade_level',
        'allocated_hours',
        'learning_model',
        'methods',
        'target_students',
        'preliminary_activities',
        'core_activities',
        'closing_activities',
        'diagnostic_assessment',
        'formative_assessment',
        'summative_assessment',
        'remedial_plan',
        'enrichment_plan',
        'student_worksheet',
        'assessment_rubric',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function learningOutcome()
    {
        return $this->belongsTo(LearningOutcome::class);
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }
}
