<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LoginBackground extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function activeUrl(): string
    {
        if (!Schema::hasTable('login_backgrounds')) {
            return asset('images/bg.png');
        }

        $active = static::query()->active()->first();

        if ($active && $active->image_path) {
            return asset($active->image_path);
        }

        return asset('images/bg.png');
    }
}
