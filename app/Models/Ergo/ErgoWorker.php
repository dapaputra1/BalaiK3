<?php

namespace App\Models\Ergo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErgoWorker extends Model
{
    use HasFactory;
    protected $table = 'ergo_workers';
    protected $guarded = ['id'];

    public function assessment()
    {
        return $this->belongsTo(ErgoAssessment::class, 'assessment_id');
    }

    public function rebaScore()
    {
        return $this->hasOne(ErgoRebaScore::class, 'worker_id');
    }
}