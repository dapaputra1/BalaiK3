<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengujian extends Model
{
    protected $table = 'pengujian';

    protected $fillable = [
        'permohonan_id',
        'status',
        'sent_to_bap_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sent_to_bap_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function lokasi()
    {
        return $this->hasMany(PengujianLokasi::class, 'pengujian_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
