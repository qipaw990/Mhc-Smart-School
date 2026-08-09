<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'semester_id',
        'name',
        'version',
        'status',
        'optimization_score',
        'created_by_user_id',
        'notes',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(ScheduleItem::class);
    }

    public function histories()
    {
        return $this->hasMany(ScheduleHistory::class)->orderBy('created_at', 'desc');
    }
}
