<?php

namespace App\Models\Ergo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErgoRebaScore extends Model
{
    use HasFactory;
    protected $table = 'ergo_reba_scores';
    protected $guarded = ['id'];

    public function worker()
    {
        return $this->belongsTo(ErgoWorker::class, 'worker_id');
    }
}