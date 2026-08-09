<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'learning_outcome_id',
        'code',
        'order_number',
        'description',
        'semester_number',
        'estimated_hours',
        'status',
    ];

    public function learningOutcome()
    {
        return $this->belongsTo(LearningOutcome::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('sequence_order');
    }

    public function pathItems()
    {
        return $this->hasMany(LearningPathItem::class);
    }

    public function teachingModules()
    {
        return $this->hasMany(TeachingModule::class);
    }
}
