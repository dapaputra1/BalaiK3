<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bap extends Model
{
    protected $table = 'baps';

    protected $fillable = [
        'permohonan_id',
        'nomor_bap',
        'status',
        'sent_to_verifikasi_at',
        'sent_to_user_at',
        'user_approved_at',
        'user_approved_by',
        'admin_viewed_at',
        'admin_viewed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sent_to_verifikasi_at' => 'datetime',
        'sent_to_user_at' => 'datetime',
        'user_approved_at' => 'datetime',
        'admin_viewed_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function items()
    {
        return $this->hasMany(BapItem::class, 'bap_id');
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
