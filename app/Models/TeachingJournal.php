<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'schedule_item_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'learning_objective_id',
        'date',
        'period_start',
        'period_end',
        'topic_activity',
        'notes_challenges',
        'photo_url',
        'student_present_count',
        'student_absent_count',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function scheduleItem()
    {
        return $this->belongsTo(ScheduleItem::class);
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

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }
}
