<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'subject_id',
        'learning_objective_id',
        'title',
        'phase',
        'description',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order_number');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
