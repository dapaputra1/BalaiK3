<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanParameter extends Model
{
    protected $fillable = [
        'permohonan_id',
        'service_parameter_id',
        'parameter_name',
        'location_name',
        'qty',
        'price',
        'note',
        'status',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function serviceParameter()
    {
        return $this->belongsTo(ServiceParameter::class);
    }
}
