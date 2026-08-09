<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'start_time',
        'submit_time',
        'duration_used_seconds',
        'status',
        'tab_switch_count',
        'total_score',
        'is_passed',
        'ip_address',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'submit_time' => 'datetime',
        'total_score' => 'decimal:2',
        'is_passed' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentExamAnswer::class);
    }
}
