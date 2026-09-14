<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HomePopup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'desktop_image_path',
        'mobile_image_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public static function resolvedSlides(): Collection
    {
        if (!Schema::hasTable('home_popups')) {
            return collect([
                [
                    'name' => 'Popup Home',
                    'desktop_url' => asset(AppSetting::getValue('home_popup_desktop_image', 'images/popup.png') ?: 'images/popup.png'),
                    'mobile_url' => asset(AppSetting::getValue('home_popup_mobile_image', 'images/popup_mobile.png') ?: 'images/popup_mobile.png'),
                ],
            ]);
        }

        $slides = static::query()
            ->active()
            ->ordered()
            ->get()
            ->map(function (self $popup): array {
                return [
                    'name' => $popup->name,
                    'desktop_url' => asset($popup->desktop_image_path),
                    'mobile_url' => asset($popup->mobile_image_path),
                ];
            });

        if ($slides->isNotEmpty()) {
            return $slides->values();
        }

        return collect([
            [
                'name' => 'Popup Home',
                'desktop_url' => asset(AppSetting::getValue('home_popup_desktop_image', 'images/popup.png') ?: 'images/popup.png'),
                'mobile_url' => asset(AppSetting::getValue('home_popup_mobile_image', 'images/popup_mobile.png') ?: 'images/popup_mobile.png'),
            ],
        ]);
    }
}
