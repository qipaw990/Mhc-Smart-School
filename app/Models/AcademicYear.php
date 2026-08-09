<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function activeSemester()
    {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'academic_year_id');
    }
}
