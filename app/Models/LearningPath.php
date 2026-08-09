<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningPath extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'academic_year_id',
        'major_id',
        'phase',
        'semester_number',
        'title',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function items()
    {
        return $this->hasMany(LearningPathItem::class)->orderBy('sequence_order');
    }
}
