<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuketK3History extends Model
{
    use HasFactory;

    protected $table = 'suket_k3_histories';

    protected $fillable = [
        'suket_id',
        'user_id',
        'action',
        'stage_before',
        'stage_after',
        'nomor_surat',
        'catatan',
        'metadata',
    ];

    protected $casts = [
        'stage_before' => 'integer',
        'stage_after' => 'integer',
        'metadata' => 'array',
    ];

    public const ACTION_LABELS = [
        'created' => 'Permohonan Didaftarkan',
        'evaluasi_approved' => 'Evaluasi LHU Disetujui',
        'evaluasi_rejected' => 'Evaluasi LHU Ditolak / Minta Revisi',
        'send_to_qc' => 'Draf Diajukan ke QC',
        'upload_draft' => 'Upload Revisi Draf Word',
        'qc_approved' => 'QC Disetujui & Penetapan Nomor Surat',
        'qc_returned' => 'QC Mengembalikan Berkas ke Penyusunan',
        'signed' => 'Pengesahan Tanda Tangan Suket',
        'published' => 'Penerbitan Suket Resmi',
        'sent_to_customer' => 'Suket Diserahkan ke Pemohon',
        'comment_added' => 'Catatan / Sorotan Ditambahkan',
    ];

    public const ACTION_BADGES = [
        'created' => 'primary',
        'evaluasi_approved' => 'success',
        'evaluasi_rejected' => 'danger',
        'send_to_qc' => 'info',
        'upload_draft' => 'secondary',
        'qc_approved' => 'success',
        'qc_returned' => 'warning',
        'signed' => 'dark',
        'published' => 'success',
        'sent_to_customer' => 'success',
        'comment_added' => 'light',
    ];

    public function suket(): BelongsTo
    {
        return $this->belongsTo(SuketK3::class, 'suket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    public function getActionBadgeAttribute(): string
    {
        return self::ACTION_BADGES[$this->action] ?? 'secondary';
    }

    public function getStageBeforeLabelAttribute(): ?string
    {
        if ($this->stage_before === null) {
            return null;
        }
        return SuketK3::STAGES[$this->stage_before]['label'] ?? "Tahap {$this->stage_before}";
    }

    public function getStageAfterLabelAttribute(): ?string
    {
        if ($this->stage_after === null) {
            return null;
        }
        return SuketK3::STAGES[$this->stage_after]['label'] ?? "Tahap {$this->stage_after}";
    }
}
