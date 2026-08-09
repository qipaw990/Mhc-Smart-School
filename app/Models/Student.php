<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id',
        'current_class_id',
        'major_id',
        'nis',
        'nisn',
        'nik',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'parent_name',
        'parent_phone',
        'email',
        'photo',
        'entry_year',
        'status',
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

    public function currentClass()
    {
        return $this->belongsTo(SchoolClass::class, 'current_class_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function parents()
    {
        return $this->belongsToMany(StudentParent::class, 'student_parents', 'student_id', 'parent_id')
                    ->withPivot('relationship');
    }

    public function histories()
    {
        return $this->hasMany(StudentHistory::class);
    }
}
