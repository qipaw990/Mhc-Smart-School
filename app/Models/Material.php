<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'learning_objective_id',
        'title',
        'description',
        'file_path',
        'video_url',
        'external_link',
        'estimated_minutes',
        'sequence_order',
    ];

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }
}
