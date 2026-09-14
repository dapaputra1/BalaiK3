<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class JejaringEntry extends Model
{
    protected $table = 'jejaring_entries';

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'category',
        'name',
        'address',
        'website',
        'sort_order',
        'is_active',
    ];

    public static function categoryDefinitions(): array
    {
        return [
            'universitas' => [
                'label' => 'Universitas',
                'route' => 'jejaring.universitas',
                'seo_title' => 'Jejaring Universitas Balai K3 Surabaya',
                'seo_description' => 'Daftar jejaring universitas Balai K3 Surabaya yang memuat nama universitas, alamat, dan tautan website resmi.',
                'seo_keywords' => 'jejaring universitas, universitas mitra, Balai K3 Surabaya, website universitas',
                'heading' => 'Jejaring Universitas',
                'lead' => 'Informasi pada halaman ini memuat daftar universitas yang menjadi bagian dari jejaring Balai K3 Surabaya.',
            ],
            'pjk3' => [
                'label' => 'PJK3',
                'route' => 'jejaring.pjk3',
                'seo_title' => 'Jejaring PJK3 Balai K3 Surabaya',
                'seo_description' => 'Daftar jejaring PJK3 Balai K3 Surabaya yang memuat nama lembaga, alamat, dan tautan website resmi.',
                'seo_keywords' => 'jejaring PJK3, mitra PJK3, Balai K3 Surabaya, lembaga PJK3',
                'heading' => 'Jejaring PJK3',
                'lead' => 'Informasi pada halaman ini memuat daftar PJK3 yang menjadi bagian dari jejaring Balai K3 Surabaya.',
            ],
            'perusahaan' => [
                'label' => 'Perusahaan',
                'route' => 'jejaring.perusahaan',
                'seo_title' => 'Jejaring Perusahaan Balai K3 Surabaya',
                'seo_description' => 'Daftar jejaring perusahaan Balai K3 Surabaya yang memuat nama perusahaan, alamat, dan tautan website resmi.',
                'seo_keywords' => 'jejaring perusahaan, perusahaan mitra, Balai K3 Surabaya',
                'heading' => 'Jejaring Perusahaan',
                'lead' => 'Informasi pada halaman ini memuat daftar perusahaan yang menjadi bagian dari jejaring Balai K3 Surabaya.',
            ],
            'instansi_wilayah_kerja' => [
                'label' => 'Instansi Wilayah Kerja',
                'route' => 'jejaring.instansi-wilayah-kerja',
                'seo_title' => 'Jejaring Instansi Wilayah Kerja Balai K3 Surabaya',
                'seo_description' => 'Daftar jejaring instansi wilayah kerja Balai K3 Surabaya yang memuat nama instansi, alamat, dan tautan website resmi.',
                'seo_keywords' => 'jejaring instansi wilayah kerja, instansi mitra, Balai K3 Surabaya',
                'heading' => 'Jejaring Instansi Wilayah Kerja',
                'lead' => 'Informasi pada halaman ini memuat daftar instansi wilayah kerja yang menjadi bagian dari jejaring Balai K3 Surabaya.',
            ],
            'instansi' => [
                'label' => 'Instansi',
                'route' => 'jejaring.instansi',
                'seo_title' => 'Jejaring Instansi Balai K3 Surabaya',
                'seo_description' => 'Daftar jejaring instansi Balai K3 Surabaya yang memuat nama instansi, alamat, dan tautan website resmi.',
                'seo_keywords' => 'jejaring instansi, instansi mitra, Balai K3 Surabaya',
                'heading' => 'Jejaring Instansi',
                'lead' => 'Informasi pada halaman ini memuat daftar instansi yang menjadi bagian dari jejaring Balai K3 Surabaya.',
            ],
        ];
    }

    public static function categoryDefinition(string $category): ?array
    {
        return static::categoryDefinitions()[$category] ?? null;
    }

    public static function categoryKeys(): array
    {
        return array_keys(static::categoryDefinitions());
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function getCategoryLabelAttribute(): string
    {
        return static::categoryDefinition((string) $this->category)['label']
            ?? Str::headline((string) $this->category);
    }

    public function getWebsiteUrlAttribute(): ?string
    {
        $website = trim((string) $this->website);
        if ($website === '') {
            return null;
        }

        if (!preg_match('~^https?://~i', $website)) {
            $website = 'https://' . ltrim($website, '/');
        }

        return $website;
    }

    public function getUploaderNameAttribute(): string
    {
        return $this->uploader?->name ?: 'Admin Balai K3';
    }
}
