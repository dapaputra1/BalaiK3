<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\ServiceSystemCode;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'system_code',
        'short_code',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saving(function (ServiceCategory $category) {
            if (!empty($category->system_code)) {
                return;
            }

            $baseCode = ServiceSystemCode::categoryCodeBase(
                (string) ($category->short_code ?? ''),
                (string) ($category->name ?? '')
            );

            $category->system_code = ServiceSystemCode::uniqueForTable(
                $category->getTable(),
                'system_code',
                $baseCode,
                $category->id
            );
        });
    }

    public function parameters()
    {
        return $this->hasMany(ServiceParameter::class, 'service_category_id');
    }

    public function packages()
    {
        return $this->hasMany(ServicePackage::class, 'service_category_id');
    }
}
