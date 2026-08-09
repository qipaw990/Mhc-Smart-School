<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'score',
        'is_remedial',
        'remedial_score',
        'final_score',
        'achievement_status',
        'teacher_notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'remedial_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'is_remedial' => 'boolean',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
