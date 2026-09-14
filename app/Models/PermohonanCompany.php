<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanCompany extends Model
{
    protected $fillable = [
        'permohonan_id',
        'company_name',
        'responsible_name',
        'company_email',
        'company_phone',
        'company_type',
        'company_province',
        'company_city',
        'company_address',
        'worker_count',
        'order_proof_path',
        'authority_same',
        'authority_name',
        'authority_role',
        'responsible_signature_path',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
}
