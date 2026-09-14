<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    protected $fillable = [
        'kode',
        'user_id',
        'status_global',
        'order_review_status',
        'order_review_original_parameters',
        'order_review_note',
        'order_reviewed_by',
        'order_reviewed_at',
        'order_review_sent_at',
        'order_review_customer_approved_at',
        'order_review_customer_approved_by',
        'status_dokumen',
        'status_lab',
        'jadwal_mulai',
        'jadwal_selesai',
        'jadwal_lokasi',
        'jadwal_catatan',
        'jadwal_pengumuman',
        'jadwal_sent_to_user_at',
        'jadwal_user_approved_at',
        'jadwal_user_approved_by',
        'penjadwalan_sent_at',
        'ma_approved_at',
        'spt_sent_at',
        'cancel_reason',
        'cancel_note',
        'cancelled_at',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
        'order_review_original_parameters' => 'array',
        'order_reviewed_at' => 'datetime',
        'order_review_sent_at' => 'datetime',
        'order_review_customer_approved_at' => 'datetime',
        'jadwal_mulai' => 'date',
        'jadwal_selesai' => 'date',
        'jadwal_sent_to_user_at' => 'datetime',
        'jadwal_user_approved_at' => 'datetime',
        'penjadwalan_sent_at' => 'datetime',
        'ma_approved_at' => 'datetime',
        'spt_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderReviewer()
    {
        return $this->belongsTo(User::class, 'order_reviewed_by');
    }

    public function company()
    {
        return $this->hasOne(PermohonanCompany::class);
    }

    public function parameters()
    {
        return $this->hasMany(PermohonanParameter::class);
    }

    public function steps()
    {
        return $this->hasMany(PermohonanStep::class);
    }

    public function approvals()
    {
        return $this->hasMany(StepApproval::class);
    }

    public function dokumenPenawaran()
    {
        return $this->hasMany(DokumenPenawaran::class);
    }

    public function assignments()
    {
        return $this->hasMany(PermohonanAssignment::class);
    }

    public function spt()
    {
        return $this->hasOne(DokumenSpt::class, 'permohonan_id');
    }

    public function pengujian()
    {
        return $this->hasOne(Pengujian::class, 'permohonan_id');
    }

    public function bap()
    {
        return $this->hasOne(Bap::class, 'permohonan_id');
    }

    public function koding()
    {
        return $this->hasOne(Koding::class, 'permohonan_id');
    }

    public function prepanalisa()
    {
        return $this->hasOne(Prepanalisa::class, 'permohonan_id');
    }

    public function draftLhu()
    {
        return $this->hasOne(DraftLhu::class, 'permohonan_id');
    }

    public function ulasanResponses()
    {
        return $this->hasMany(UlasanPermohonanResponse::class);
    }
}
