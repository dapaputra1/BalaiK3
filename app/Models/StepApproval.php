<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StepApproval extends Model
{
    protected $fillable = [
        'permohonan_id',
        'step_id',
        'role',
        'status',
        'note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
