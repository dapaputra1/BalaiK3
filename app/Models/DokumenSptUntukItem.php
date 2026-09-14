<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenSptUntukItem extends Model
{
    protected $fillable = [
        'spt_id',
        'nomor',
        'uraian',
        'urutan',
    ];

    public function spt()
    {
        return $this->belongsTo(DokumenSpt::class, 'spt_id');
    }
}
