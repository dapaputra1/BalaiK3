<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErgoAssessment extends Model
{
    use HasFactory;

    protected $table = 'ergo_assessments';
    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(\App\Models\Ergo\ErgoCompany::class, 'company_id');
    }

    public function worker()
    {
        return $this->hasOne(\App\Models\Ergo\ErgoWorker::class, 'assessment_id');
    }

    public function photos()
    {
        return $this->hasMany(ErgoAssessmentPhoto::class, 'assessment_id');
    }
}