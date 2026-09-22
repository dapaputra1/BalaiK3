<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErgoAssessmentPhoto extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'landmarks_json' => 'array',
    ];

    public function assessment()
    {
        return $this->belongsTo(ErgoAssessment::class, 'assessment_id');
    }
}