<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanAssignment extends Model
{
    protected $fillable = [
        'permohonan_id',
        'user_id',
        'role',
        'is_leader',
    ];

    protected $casts = [
        'is_leader' => 'boolean',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
