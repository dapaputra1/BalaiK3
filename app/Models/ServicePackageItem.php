<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackageItem extends Model
{
    protected $fillable = [
        'service_package_id',
        'service_parameter_id',
        'label',
        'sort_order',
    ];

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ServiceParameter::class, 'service_parameter_id');
    }
}
