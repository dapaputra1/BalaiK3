<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prepanalisa extends Model
{
    protected $table = 'prepanalisisas';

    protected $fillable = [
        'permohonan_id',
        'status',
        'sent_to_analis_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sent_to_analis_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function items()
    {
        return $this->hasMany(PrepanalisaItem::class, 'prepanalisa_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
