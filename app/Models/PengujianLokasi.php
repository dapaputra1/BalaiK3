<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengujianLokasi extends Model
{
    protected $table = 'pengujian_lokasi';

    protected $fillable = [
        'pengujian_id',
        'nama_lokasi',
        'urutan',
    ];

    public function pengujian()
    {
        return $this->belongsTo(Pengujian::class, 'pengujian_id');
    }

    public function dokumen()
    {
        return $this->hasMany(PengujianDokumen::class, 'lokasi_id');
    }
}
