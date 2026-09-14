<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPenawaran extends Model
{
    protected $table = 'dokumen_penawaran';

    protected $fillable = [
        'permohonan_id',
        'nomor_surat',
        'catatan',
        'file_path',
        'status',
        'sent_at',
        'received_at',
        'signed_file_path',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
}
