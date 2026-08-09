<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_exam_id',
        'question_id',
        'answer_json',
        'is_correct',
        'is_doubtful',
        'score_obtained',
        'teacher_notes',
    ];

    protected $casts = [
        'answer_json' => 'array',
        'is_correct' => 'boolean',
        'is_doubtful' => 'boolean',
        'score_obtained' => 'decimal:2',
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
