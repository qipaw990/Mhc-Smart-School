<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'major_id',
        'code',
        'name',
        'group',
        'phase',
        'type',
        'hours_per_week',
        'total_hours',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function learningOutcomes()
    {
        return $this->hasMany(LearningOutcome::class);
    }

    public function learningPaths()
    {
        return $this->hasMany(LearningPath::class);
    }

    public function teachingModules()
    {
        return $this->hasMany(TeachingModule::class);
    }

    public function getGroupLabelAttribute()
    {
        return match($this->group) {
            'A_general' => 'Muatan Umum (A)',
            'B_vocational' => 'Kejuruan / Dasar Program (B)',
            'C_concentration' => 'Konsentrasi Keahlian (C)',
            'mulok' => 'Muatan Lokal',
            'p5' => 'Projek Penguatan P5',
            default => 'Lainnya',
        };
    }
}
