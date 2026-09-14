<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengujianDokumenParameter extends Model
{
    protected $table = 'pengujian_dokumen_parameters';

    protected $fillable = [
        'dokumen_id',
        'service_parameter_id',
        'qty',
        'is_direct',
        'is_sesuai',
        'urutan',
    ];

    protected $casts = [
        'is_direct' => 'boolean',
        'is_sesuai' => 'boolean',
    ];

    public function dokumen()
    {
        return $this->belongsTo(PengujianDokumen::class, 'dokumen_id');
    }

    public function serviceParameter()
    {
        return $this->belongsTo(ServiceParameter::class, 'service_parameter_id');
    }
}
