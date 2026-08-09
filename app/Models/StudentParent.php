<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentParent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'father_name',
        'father_nik',
        'father_occupation',
        'father_phone',
        'mother_name',
        'mother_nik',
        'mother_occupation',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_parents', 'parent_id', 'student_id')
                    ->withPivot('relationship');
    }
}
