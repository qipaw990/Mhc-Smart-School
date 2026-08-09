<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_code',
        'name',
        'npsn',
        'nss',
        'address',
        'village',
        'district',
        'regency',
        'province',
        'postal_code',
        'phone',
        'email',
        'website',
        'logo',
        'principal_name',
        'accreditation',
        'vision',
        'mission',
    ];

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
