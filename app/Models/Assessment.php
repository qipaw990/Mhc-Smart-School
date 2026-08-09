<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'semester_id',
        'teacher_id',
        'subject_id',
        'class_id',
        'learning_objective_id',
        'title',
        'type',
        'kktp_score',
        'max_score',
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'kktp_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'diagnostic' => 'Asesmen Diagnostik',
            'formative' => 'Asesmen Formatif',
            'summative_tp' => 'Sumatif Lingkup Materi (TP)',
            'summative_semester' => 'Sumatif Akhir Semester (SAS)',
            default => 'Asesmen Formatif',
        };
    }
}
