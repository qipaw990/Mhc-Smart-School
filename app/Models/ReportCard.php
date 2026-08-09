<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'semester_id',
        'student_id',
        'class_id',
        'sick_count',
        'permit_count',
        'absent_count',
        'homeroom_notes',
        'promotion_status',
        'status',
        'class_rank',
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

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function grades()
    {
        return $this->hasMany(ReportCardGrade::class)->whereHas('subject');
    }

    public function extracurriculars()
    {
        return $this->hasMany(ReportCardExtracurricular::class);
    }

    public function getAverageScoreAttribute()
    {
        return $this->grades->avg('final_score') ?? 0;
    }
}
