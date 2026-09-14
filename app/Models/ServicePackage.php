<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'short_code',
        'badge',
        'subtitle',
        'price',
        'unit',
        'sort_order',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function items()
    {
        return $this->hasMany(ServicePackageItem::class, 'service_package_id');
    }
}
