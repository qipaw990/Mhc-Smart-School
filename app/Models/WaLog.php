<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'recipient_name',
        'message',
        'type',
        'status',
        'response_info',
    ];

    public function scopeSearch($query, $search)
    {
        if (empty($search)) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('phone', 'like', "%{$search}%")
              ->orWhere('recipient_name', 'like', "%{$search}%")
              ->orWhere('message', 'like', "%{$search}%");
        });
    }
}
