<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'urutan',
    ];

    public function permohonanSteps()
    {
        return $this->hasMany(PermohonanStep::class, 'step_id');
    }
}
