<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'schedule_item_id',
        'student_id',
        'teacher_id',
        'date',
        'time',
        'type',
        'method',
        'status',
        'latitude',
        'longitude',
        'device_info',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function scheduleItem()
    {
        return $this->belongsTo(ScheduleItem::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedByTeacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'H' => 'Hadir',
            'S' => 'Sakit',
            'I' => 'Izin',
            'A' => 'Alpa',
            'T' => 'Terlambat',
            'D' => 'Dispensasi',
            'P' => 'PKL / Magang',
            default => 'Hadir',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'H' => 'success',
            'S' => 'info',
            'I' => 'warning text-dark',
            'A' => 'danger',
            'T' => 'secondary',
            'D' => 'primary',
            'P' => 'dark',
            default => 'success',
        };
    }
}
