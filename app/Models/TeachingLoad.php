<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingLoad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'semester_id',
        'teacher_id',
        'subject_id',
        'class_id',
        'hours_per_week',
        'preferred_room_id',
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

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function preferredRoom()
    {
        return $this->belongsTo(Room::class, 'preferred_room_id');
    }

    public function scheduleItems()
    {
        return $this->hasMany(ScheduleItem::class);
    }
}
