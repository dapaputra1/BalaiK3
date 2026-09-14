<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftLhu extends Model
{
    protected $table = 'draft_lhus';

    protected $fillable = [
        'permohonan_id',
        'tables',
        'final_file_path',
        'final_file_name',
        'final_uploaded_by',
        'final_uploaded_at',
        'signed_file_path',
        'signed_file_name',
        'signed_uploaded_by',
        'signed_uploaded_at',
        'surat_tagihan_generated_by',
        'surat_tagihan_generated_at',
        'invoice_file_path',
        'invoice_file_name',
        'invoice_generated_by',
        'invoice_generated_at',
        'invoice_verified_by_user_id',
        'invoice_verified_by_user_at',
        'billing_file_path',
        'billing_file_name',
        'billing_uploaded_by',
        'billing_uploaded_at',
        'billing_sent_by',
        'billing_sent_at',
        'billing_expires_at',
        'billing_payment_proof_path',
        'billing_payment_proof_name',
        'billing_payment_proof_uploaded_by',
        'billing_payment_proof_uploaded_at',
        'billing_paid_by_user_id',
        'billing_paid_by_user_at',
        'billing_verified_by',
        'billing_verified_at',
        'suket_file_path',
        'suket_file_name',
        'suket_uploaded_by',
        'suket_uploaded_at',
        'lhu_sent_to_user_by',
        'lhu_sent_to_user_at',
        'lhu_user_approved_by',
        'lhu_user_approved_at',
        'lhu_user_revision_at',
        'lhu_user_revision_note',
        'qc_revision_file_path',
        'qc_revision_file_name',
        'qc_revision_file_uploaded_by',
        'qc_revision_file_uploaded_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tables' => 'array',
        'final_uploaded_at' => 'datetime',
        'signed_uploaded_at' => 'datetime',
        'surat_tagihan_generated_at' => 'datetime',
        'invoice_generated_at' => 'datetime',
        'invoice_verified_by_user_at' => 'datetime',
        'billing_uploaded_at' => 'datetime',
        'billing_sent_at' => 'datetime',
        'billing_expires_at' => 'datetime',
        'billing_payment_proof_uploaded_at' => 'datetime',
        'billing_paid_by_user_at' => 'datetime',
        'billing_verified_at' => 'datetime',
        'suket_uploaded_at' => 'datetime',
        'lhu_sent_to_user_at' => 'datetime',
        'lhu_user_approved_at' => 'datetime',
        'lhu_user_revision_at' => 'datetime',
        'qc_revision_file_uploaded_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }
}
