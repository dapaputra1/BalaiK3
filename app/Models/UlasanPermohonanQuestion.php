<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UlasanPermohonanQuestion extends Model
{
    protected $fillable = [
        'question',
        'type',
        'category',
        'is_active',
        'sort_order',
        'note',
        'rating_labels',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating_labels' => 'array',
    ];

    public function responses()
    {
        return $this->hasMany(UlasanPermohonanResponse::class, 'question_id');
    }
}
