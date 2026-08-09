<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'time_slot_id',
        'teaching_load_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'room_id',
        'day',
        'period',
        'consecutive_hours',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function teachingLoad()
    {
        return $this->belongsTo(TeachingLoad::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
