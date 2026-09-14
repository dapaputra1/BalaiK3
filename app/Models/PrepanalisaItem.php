<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepanalisaItem extends Model
{
    protected $table = 'prepanalisa_items';

    protected $fillable = [
        'prepanalisa_id',
        'koding_item_id',
        'pengujian_dokumen_parameter_id',
        'service_parameter_id',
        'kode_koding',
        'data_skpm',
        'data_hasil_baca',
        'data_hasil_perhitungan',
        'assigned_user_id',
        'is_done',
        'verif_status',
        'verif_note',
        'verif_by',
        'verif_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_skpm' => 'array',
        'data_hasil_baca' => 'array',
        'data_hasil_perhitungan' => 'array',
        'is_done' => 'boolean',
        'verif_at' => 'datetime',
    ];

    public function prepanalisa()
    {
        return $this->belongsTo(Prepanalisa::class, 'prepanalisa_id');
    }

    public function kodingItem()
    {
        return $this->belongsTo(KodingItem::class, 'koding_item_id');
    }

    public function pengujianDokumenParameter()
    {
        return $this->belongsTo(PengujianDokumenParameter::class, 'pengujian_dokumen_parameter_id');
    }

    public function serviceParameter()
    {
        return $this->belongsTo(ServiceParameter::class, 'service_parameter_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function verifUser()
    {
        return $this->belongsTo(User::class, 'verif_by');
    }
}
