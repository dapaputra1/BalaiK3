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
        'qc_at' => 'datetime',
        'signed_at' => 'datetime',
        'published_at' => 'datetime',
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
            'code' => 'penyerahan_suket',
            'label' => 'Penyerahan Suket',
            'desc' => 'Penyerahan digital ke akun pemohon melalui portal web Balai K3.',
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
}
