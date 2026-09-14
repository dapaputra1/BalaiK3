<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterLod extends Model
{
    protected $fillable = [
        'service_parameter_id',
        'kons',
        'vol',
        'waktu',
        'fr',
        'sk',
        'pm',
        'factor_ppm',
        'factor_ugm3',
        'sample_kons',
        'sample_vol',
        'sample_waktu',
        'sample_fr',
        'sample_sk',
        'sample_pm',
        'sample_ppm',
        'sample_ugm3',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'kons' => 'decimal:4',
        'vol' => 'decimal:4',
        'waktu' => 'decimal:2',
        'fr' => 'decimal:4',
        'sk' => 'decimal:4',
        'pm' => 'decimal:2',
        'factor_ppm' => 'decimal:6',
        'factor_ugm3' => 'decimal:6',
        'sample_kons' => 'decimal:4',
        'sample_vol' => 'decimal:4',
        'sample_waktu' => 'decimal:2',
        'sample_fr' => 'decimal:4',
        'sample_sk' => 'decimal:4',
        'sample_pm' => 'decimal:2',
        'sample_ppm' => 'decimal:4',
        'sample_ugm3' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function serviceParameter()
    {
        return $this->belongsTo(ServiceParameter::class, 'service_parameter_id');
    }
}
