<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCardGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_card_id',
        'subject_id',
        'teacher_id',
        'final_score',
        'predicate',
        'highest_competency_desc',
        'lowest_competency_desc',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
    ];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
