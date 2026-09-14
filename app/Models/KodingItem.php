<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodingItem extends Model
{
    protected $table = 'koding_items';

    protected $fillable = [
        'koding_id',
        'pengujian_dokumen_id',
        'pengujian_dokumen_parameter_id',
        'kode',
    ];

    public function koding()
    {
        return $this->belongsTo(Koding::class, 'koding_id');
    }

    public function pengujianDokumen()
    {
        return $this->belongsTo(PengujianDokumen::class, 'pengujian_dokumen_id');
    }

    public function pengujianDokumenParameter()
    {
        return $this->belongsTo(\App\Models\PengujianDokumenParameter::class, 'pengujian_dokumen_parameter_id');
    }
}
