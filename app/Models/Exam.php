<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'semester_id',
        'question_bank_id',
        'teacher_id',
        'subject_id',
        'title',
        'token',
        'start_time',
        'end_time',
        'duration_minutes',
        'kktp_score',
        'randomize_questions',
        'randomize_options',
        'max_tab_switches',
        'status',
        'instructions',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'kktp_score' => 'decimal:2',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
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

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function examClasses()
    {
        return $this->hasMany(ExamClass::class);
    }

    public function studentExams()
    {
        return $this->hasMany(StudentExam::class);
    }
}
