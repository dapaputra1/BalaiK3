<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanStep extends Model
{
    protected $fillable = [
        'permohonan_id',
        'step_id',
        'status',
        'note',
        'started_at',
        'finished_at',
        'updated_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
