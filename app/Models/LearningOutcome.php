<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningOutcome extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'academic_year_id',
        'phase',
        'code',
        'element',
        'description',
        'status',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function learningObjectives()
    {
        return $this->hasMany(LearningObjective::class)->orderBy('order_number');
    }

    public function teachingModules()
    {
        return $this->hasMany(TeachingModule::class);
    }
}
