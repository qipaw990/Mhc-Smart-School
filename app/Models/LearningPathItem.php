<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPathItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_path_id',
        'learning_objective_id',
        'sequence_order',
        'week_number',
        'hour_allocation',
        'topic',
        'assessment_plan',
    ];

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }
}
