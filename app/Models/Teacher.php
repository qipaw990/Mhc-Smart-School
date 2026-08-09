<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id',
        'nip',
        'nuptk',
        'nik',
        'name',
        'title_prefix',
        'title_suffix',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone',
        'email',
        'education',
        'major',
        'employment_status',
        'position',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeroomClasses()
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function teachingLoads()
    {
        return $this->hasMany(TeachingLoad::class);
    }

    public function getFullNameAttribute()
    {
        $prefix = $this->title_prefix ? $this->title_prefix . ' ' : '';
        $suffix = $this->title_suffix ? ', ' . $this->title_suffix : '';
        return $prefix . $this->name . $suffix;
    }
}
