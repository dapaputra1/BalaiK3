<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuketK3 extends Model
{
    use HasFactory;

    protected $table = 'suket_k3s';

    protected $fillable = [
        'user_id',
        'permohonan_id',
        'nomor_order',
        'status_tahap',
        'faktor_k3',
        'lhu_source',
        'lhu_file_path',
        'lhu_file_name',
        'foto_pengujian_path',
        'foto_pengujian_name',
        'denah_lokasi_path',
        'denah_lokasi_name',
        'perusahaan_nama',
        'lokasi',
        'catatan',
        'catatan_evaluasi',
        'catatan_revisi_pemohon',
        'revisi_pemohon_at',
        'evaluasi_status',
        'evaluasi_by',
        'evaluasi_at',
        'qc_status',
        'qc_note',
        'qc_by',
        'qc_at',
        'draft_file_path',
        'draft_file_name',
        'signed_file_path',
        'signed_file_name',
        'nomor_surat',
        'tanggal_surat',
        'signed_at',
        'signed_by',
        'published_at',
        'published_by',
        'surat_tagihan_file_path',
        'surat_tagihan_file_name',
        'surat_tagihan_nominal',
        'surat_tagihan_sent_at',
        'surat_tagihan_sent_by',
        'surat_tagihan_acc_at',
        'surat_tagihan_acc_by',
        'billing_kode',
        'billing_file_path',
        'billing_file_name',
        'billing_sent_at',
        'billing_sent_by',
        'billing_expires_at',
        'billing_guide_path',
        'billing_guide_name',
        'billing_proof_path',
        'billing_proof_name',
        'billing_proof_status',
        'billing_proof_rejected_at',
        'billing_proof_rejected_by',
        'billing_proof_reject_note',
        'billing_paid_at',
        'billing_verified_at',
        'billing_verified_by',
        'kuitansi_nomor',
        'kuitansi_file_path',
        'kuitansi_file_name',
        'kuitansi_generated_at',
        'kuitansi_generated_by',
        'kuitansi_sent_at',
        'kuitansi_sent_by',
        'sent_to_customer_at',
        'sent_to_customer_by',
        'resi_pengiriman',
        'metode_pengiriman',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status_tahap' => 'integer',
        'faktor_k3' => 'array',
        'tanggal_surat' => 'date',
        'evaluasi_at' => 'datetime',
        'revisi_pemohon_at' => 'datetime',
        'qc_at' => 'datetime',
        'signed_at' => 'datetime',
        'published_at' => 'datetime',
        'surat_tagihan_nominal' => 'decimal:2',
        'surat_tagihan_sent_at' => 'datetime',
        'surat_tagihan_acc_at' => 'datetime',
        'billing_sent_at' => 'datetime',
        'billing_expires_at' => 'datetime',
        'billing_proof_rejected_at' => 'datetime',
        'billing_paid_at' => 'datetime',
        'billing_verified_at' => 'datetime',
        'kuitansi_generated_at' => 'datetime',
        'kuitansi_sent_at' => 'datetime',
        'sent_to_customer_at' => 'datetime',
    ];

    public const FAKTOR_OPTIONS = [
        'fisika' => 'Faktor Fisika (Kebisingan, Iklim Kerja, Penerangan, Getaran)',
        'kimia' => 'Faktor Kimia (Debu, Gas, Uap, Logam Berat)',
        'biologi' => 'Faktor Biologi (Jamur, Bakteri, Angka Kuman)',
        'ergonomi' => 'Faktor Ergonomi (Postur Kerja, Manual Handling)',
        'psikologi' => 'Faktor Psikologi (Beban Kerja, Stres Kerja)',
    ];

    public const STAGES = [
        1 => [
            'code' => 'permohonan',
            'label' => 'Permohonan',
            'desc' => 'Pengajuan suket K3 oleh pemohon dengan pilihan faktor pengujian & lampiran LHU.',
            'roles' => ['user', 'admin', 'superadmin', 'pcu', 'penguji_k3'],
            'badge' => 'primary',
            'icon' => 'bi-file-earmark-plus',
        ],
        2 => [
            'code' => 'evaluasi_dokumen',
            'label' => 'Evaluasi Dokumen',
            'desc' => 'Evaluasi hasil uji berdasarkan dokumen LHU, foto pengujian, dan denah lokasi.',
            'roles' => ['pcu', 'penguji_k3', 'admin', 'superadmin'],
            'badge' => 'warning',
            'icon' => 'bi-clipboard-check',
        ],
        3 => [
            'code' => 'penyusunan_suket',
            'label' => 'Penyusunan Suket',
            'desc' => 'Auto generate draft Suket standar Permenaker 05/2018, penomoran resmi & review internal.',
            'roles' => ['admin', 'superadmin', 'pcu', 'penguji_k3'],
            'badge' => 'info',
            'icon' => 'bi-pencil-square',
        ],
        4 => [
            'code' => 'penandatanganan_suket',
            'label' => 'Penandatanganan Suket',
            'desc' => 'Pengesahan dan penandatanganan berkas ber-Nomor Surat (TTE / tanda tangan basah) oleh Kepala Balai & Admin.',
            'roles' => ['mp', 'kepala_balai', 'admin', 'superadmin'],
            'badge' => 'dark',
            'icon' => 'bi-pen',
        ],
        5 => [
            'code' => 'penerbitan_suket',
            'label' => 'Penerbitan Suket',
            'desc' => 'Verifikasi berkas bertanda tangan dan penerbitan sah oleh Administrator.',
            'roles' => ['admin', 'superadmin'],
            'badge' => 'success',
            'icon' => 'bi-award',
        ],
        6 => [
            'code' => 'surat_tagihan',
            'label' => 'Surat Tagihan',
            'desc' => 'Penerbitan surat tagihan resmi suket K3 dan konfirmasi ACC oleh pemohon.',
            'roles' => ['bendahara', 'admin', 'superadmin'],
            'badge' => 'primary',
            'icon' => 'bi-envelope-paper',
        ],
        7 => [
            'code' => 'kode_billing',
            'label' => 'Kode Billing',
            'desc' => 'Penerbitan kode billing Simponi/PNBP, pembayaran pemohon, & verifikasi bendahara.',
            'roles' => ['bendahara', 'admin', 'superadmin'],
            'badge' => 'warning',
            'icon' => 'bi-upc',
        ],
        8 => [
            'code' => 'kuitansi',
            'label' => 'Kuitansi',
            'desc' => 'Penerbitan dan penerusan berkas kuitansi lunas resmi ke akun pemohon.',
            'roles' => ['bendahara', 'admin', 'superadmin'],
            'badge' => 'info',
            'icon' => 'bi-receipt',
        ],
        9 => [
            'code' => 'penyerahan_suket',
            'label' => 'Penyerahan Suket',
            'desc' => 'Penyerahan digital Surat Keterangan K3 resmi ke akun pemohon melalui portal web Balai K3.',
            'roles' => ['admin', 'superadmin'],
            'badge' => 'success',
            'icon' => 'bi-send-check',
        ],
    ];

    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->status_tahap]['label'] ?? 'Tidak Diketahui';
    }

    public function getStageBadgeAttribute(): string
    {
        return self::STAGES[$this->status_tahap]['badge'] ?? 'light';
    }

    public function getStageIconAttribute(): string
    {
        return self::STAGES[$this->status_tahap]['icon'] ?? 'bi-circle';
    }

    public function canRoleProcess(?string $role): bool
    {
        if (!$role) {
            return false;
        }
        if ($role === 'superadmin') {
            return true;
        }
        $allowed = self::STAGES[$this->status_tahap]['roles'] ?? [];
        return in_array($role, $allowed, true);
    }

    public function canRoleProcessQc(?string $role): bool
    {
        if (!$role) {
            return false;
        }
        return in_array($role, ['qc', 'admin', 'superadmin'], true);
    }

    public function isQcApproved(): bool
    {
        return $this->qc_status === 'approved';
    }

    public function isEvaluasiRejected(): bool
    {
        return $this->evaluasi_status === 'rejected';
    }

    public function getEffectiveLhuPathAttribute(): ?string
    {
        if (!empty($this->lhu_file_path)) {
            return $this->lhu_file_path;
        }
        return $this->permohonan?->draftLhu?->signed_file_path 
            ?: $this->permohonan?->draftLhu?->final_file_path;
    }

    public function hasLhuDocument(): bool
    {
        return !empty($this->effective_lhu_path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SuketK3Comment::class, 'suket_id')->latest();
    }

    public function lhuComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SuketK3Comment::class, 'suket_id')
            ->where(function ($q) {
                $q->where('document_type', 'lhu')->orWhereNull('document_type');
            })
            ->latest();
    }

    public function suketComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SuketK3Comment::class, 'suket_id')
            ->where('document_type', 'suket')
            ->latest();
    }

    public function pemohonComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SuketK3Comment::class, 'suket_id')
            ->where(function ($q) {
                $q->where('target', '!=', 'internal')->orWhereNull('target');
            })
            ->where(function ($q) {
                $q->where('document_type', 'lhu')->orWhereNull('document_type');
            })
            ->where('document_type', '!=', 'suket')
            ->latest();
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluasi_by');
    }

    public function qcUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function tagihanSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surat_tagihan_sent_by');
    }

    public function tagihanAccUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surat_tagihan_acc_by');
    }

    public function billingSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'billing_sent_by');
    }

    public function billingVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'billing_verified_by');
    }

    public function billingProofRejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'billing_proof_rejected_by');
    }

    public function kuitansiGenerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kuitansi_generated_by');
    }

    public function kuitansiSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kuitansi_sent_by');
    }

    public function isTagihanSent(): bool
    {
        return !empty($this->surat_tagihan_sent_at);
    }

    public function isTagihanAcc(): bool
    {
        return !empty($this->surat_tagihan_acc_at);
    }

    public function isBillingSent(): bool
    {
        return !empty($this->billing_sent_at);
    }

    public function isBillingPaid(): bool
    {
        return !empty($this->billing_paid_at) || !empty($this->billing_proof_path);
    }

    public function isBillingVerified(): bool
    {
        return !empty($this->billing_verified_at);
    }

    public function isBillingProofRejected(): bool
    {
        return $this->billing_proof_status === 'rejected';
    }

    public function isBillingProofPending(): bool
    {
        return $this->isBillingPaid() && !$this->isBillingVerified() && $this->billing_proof_status !== 'rejected';
    }

    public function effectiveBillingGuidePath(): ?string
    {
        if (!empty($this->billing_guide_path) && \Illuminate\Support\Facades\Storage::disk('local')->exists($this->billing_guide_path)) {
            return $this->billing_guide_path;
        }

        $activeGuide = app(\App\Services\BillingGuideService::class)->getActiveGuide();
        return $activeGuide?->file_path;
    }

    public function effectiveBillingGuideName(): ?string
    {
        if (!empty($this->billing_guide_name)) {
            return $this->billing_guide_name;
        }

        $activeGuide = app(\App\Services\BillingGuideService::class)->getActiveGuide();
        return $activeGuide?->file_name ?: 'Panduan_Pembayaran_SIMPONI.pdf';
    }

    public function hasBillingGuide(): bool
    {
        return !empty($this->effectiveBillingGuidePath());
    }

    public function isKuitansiSent(): bool
    {
        return !empty($this->kuitansi_sent_at);
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SuketK3History::class, 'suket_id')->latest('id');
    }

    public function recordHistory(
        string $action,
        ?int $stageBefore,
        ?int $stageAfter,
        ?string $catatan = null,
        ?string $nomorSurat = null,
        ?array $metadata = null,
        ?int $userId = null
    ): SuketK3History {
        return $this->histories()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'stage_before' => $stageBefore,
            'stage_after' => $stageAfter,
            'nomor_surat' => $nomorSurat ?? $this->nomor_surat,
            'catatan' => $catatan,
            'metadata' => $metadata,
        ]);
    }
}
