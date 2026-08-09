<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCardExtracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_card_id',
        'activity_name',
        'predicate',
        'description',
    ];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class);
    }
}
