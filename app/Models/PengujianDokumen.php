<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengujianDokumen extends Model
{
    protected $table = 'pengujian_dokumen';

    protected $fillable = [
        'lokasi_id',
        'label',
        'urutan',
    ];

    public function lokasi()
    {
        return $this->belongsTo(PengujianLokasi::class, 'lokasi_id');
    }

    public function files()
    {
        return $this->hasMany(PengujianDokumenFile::class, 'dokumen_id');
    }

    public function parameters()
    {
        return $this->hasMany(PengujianDokumenParameter::class, 'dokumen_id');
    }
}
