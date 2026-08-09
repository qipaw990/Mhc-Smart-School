<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P5ProjectDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'p5_project_id',
        'dimension_name',
        'element',
        'sub_element',
        'target_phase',
    ];

    public function project()
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    public function studentScores()
    {
        return $this->hasMany(P5StudentScore::class);
    }
}
