<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenSpt extends Model
{
    protected $fillable = [
        'permohonan_id',
        'nomor_surat',
        'tempat_terbit',
        'tanggal_terbit',
        'created_by',
        'signed_file_path',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function dasarItems()
    {
        return $this->hasMany(DokumenSptDasarItem::class, 'spt_id')->orderBy('urutan');
    }

    public function untukItems()
    {
        return $this->hasMany(DokumenSptUntukItem::class, 'spt_id')->orderBy('urutan');
    }
}
