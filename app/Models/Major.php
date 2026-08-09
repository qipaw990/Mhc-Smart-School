<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'head_of_major',
        'description',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'major_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'major_id');
    }
}
