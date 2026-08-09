<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'action',
        'notes',
        'created_at',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
