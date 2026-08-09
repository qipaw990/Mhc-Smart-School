<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5StudentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'p5_project_dimension_id',
        'student_id',
        'score',
        'teacher_notes',
    ];

    public function projectDimension()
    {
        return $this->belongsTo(P5ProjectDimension::class, 'p5_project_dimension_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getScoreLabelAttribute()
    {
        return match($this->score) {
            'MB' => 'Mulai Berkembang',
            'SB' => 'Sedang Berkembang',
            'BSH' => 'Berkembang Sesuai Harapan',
            'SAB' => 'Sangat Berkembang',
            default => 'Berkembang Sesuai Harapan',
        };
    }
}
