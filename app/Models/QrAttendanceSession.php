<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrAttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_item_id',
        'teacher_id',
        'token',
        'expires_at',
        'refresh_interval_sec',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function scheduleItem()
    {
        return $this->belongsTo(ScheduleItem::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
