<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BapItem extends Model
{
    protected $table = 'bap_items';

    protected $fillable = [
        'bap_id',
        'pengujian_dokumen_parameter_id',
        'catatan',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function bap()
    {
        return $this->belongsTo(Bap::class, 'bap_id');
    }

    public function pengujianDokumenParameter()
    {
        return $this->belongsTo(PengujianDokumenParameter::class, 'pengujian_dokumen_parameter_id');
    }
}
