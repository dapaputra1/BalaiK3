<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\ServiceSystemCode;

class ServiceParameter extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'system_code',
        'short_code',
        'price',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (ServiceParameter $parameter) {
            if (!empty($parameter->system_code)) {
                return;
            }

            $categoryCode = null;
            if ($parameter->relationLoaded('category')) {
                $categoryCode = $parameter->category?->system_code;
            }
            if (!$categoryCode && !empty($parameter->service_category_id)) {
                $categoryCode = ServiceCategory::query()
                    ->whereKey($parameter->service_category_id)
                    ->value('system_code');
            }

            $baseCode = ServiceSystemCode::parameterCodeBase(
                $categoryCode,
                (string) ($parameter->short_code ?? ''),
                (string) ($parameter->name ?? '')
            );

            $parameter->system_code = ServiceSystemCode::uniqueForTable(
                $parameter->getTable(),
                'system_code',
                $baseCode,
                $parameter->id
            );
        });
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function parameterLod()
    {
        return $this->hasOne(ParameterLod::class, 'service_parameter_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'service_parameter_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'service_parameter_id');
    }

    public function permohonanParameters()
    {
        return $this->hasMany(PermohonanParameter::class, 'service_parameter_id');
    }

    public function pengujianDokumenParameters()
    {
        return $this->hasMany(PengujianDokumenParameter::class, 'service_parameter_id');
    }

    public function prepanalisaItems()
    {
        return $this->hasMany(PrepanalisaItem::class, 'service_parameter_id');
    }
}
