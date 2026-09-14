<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Koding extends Model
{
    protected $table = 'kodings';

    protected $fillable = [
        'permohonan_id',
        'status',
        'sent_to_prepanalisa_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sent_to_prepanalisa_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function items()
    {
        return $this->hasMany(KodingItem::class, 'koding_id');
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
