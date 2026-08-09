<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'type',
        'cognitive_level',
        'difficulty',
        'question_text',
        'media_url',
        'code_snippet',
        'score_weight',
        'order_number',
        'explanation',
    ];

    protected $casts = [
        'score_weight' => 'decimal:2',
    ];

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentExamAnswer::class);
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'pg' => 'Pilihan Ganda',
            'pgk' => 'Pilihan Ganda Kompleks',
            'true_false' => 'Benar / Salah',
            'matching' => 'Menjodohkan',
            'short_answer' => 'Isian Singkat',
            'essay' => 'Essay / Uraian',
            default => 'Pilihan Ganda',
        };
    }
}
