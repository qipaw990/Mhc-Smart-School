<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'period',
        'start_time',
        'end_time',
        'is_break',
        'label',
    ];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    public function scheduleItems()
    {
        return $this->hasMany(ScheduleItem::class);
    }
}
