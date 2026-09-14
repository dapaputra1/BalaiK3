<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\DokumenPenawaran;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\ServiceParameter;
use App\Models\UlasanPermohonanQuestion;
use App\Models\UlasanPermohonanResponse;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\BillingGuideService;
use App\Support\SafeDocumentUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiwayatPelayananController extends Controller
{
    private const PRIVATE_DISK = 'local';

    private const LEGACY_DISK = 'public';

    public function index(BillingGuideService $billingGuideService)
    {
        $this->normalizeCancelledNonTestableOrders((int) auth()->id());

        $ulasanQuestions = UlasanPermohonanQuestion::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'question', 'type', 'category', 'sort_order', 'note', 'rating_labels']);

        $permohonans = Permohonan::with([
            'company',
            'parameters.serviceParameter.category',
            'dokumenPenawaran',
            'bap',
            'draftLhu',
            'steps.step',
            'assignments.user',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $submittedUlasanLookup = UlasanPermohonanResponse::query()
            ->where('user_id', auth()->id())
            ->whereIn('permohonan_id', $permohonans->pluck('id'))
            ->whereNotNull('permohonan_id')
            ->select('permohonan_id')
            ->distinct()
            ->pluck('permohonan_id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();

        $originalParameterLookup = ServiceParameter::query()
            ->with('category:id,name')
            ->whereIn(
                'id',
                $permohonans->flatMap(function (Permohonan $permohonan) {
                    return collect($permohonan->order_review_original_parameters ?? [])
                        ->pluck('service_parameter_id')
                        ->filter();
                })->map(fn ($id) => (int) $id)->unique()->values()
            )
            ->get(['id', 'service_category_id', 'name', 'price'])
            ->keyBy('id');

        $riwayat = $permohonans->map(function ($permohonan) use ($submittedUlasanLookup, $originalParameterLookup) {
            $company = $permohonan->company;
            $orderParameters = $permohonan->parameters->map(function ($param) {
                return [
                    'service_parameter_id' => $param->service_parameter_id,
                    'nama' => $param->parameter_name,
                    'kategori' => $param->serviceParameter?->category?->name ?? '-',
                    'qty' => $param->qty,
                    'harga' => (float) $param->price,
                ];
            })->values();
            $originalReviewParameters = collect($permohonan->order_review_original_parameters ?? [])
                ->map(function ($item) use ($originalParameterLookup) {
                    $serviceParameterId = (int) ($item['service_parameter_id'] ?? 0);
                    $serviceParameter = $serviceParameterId > 0
                        ? $originalParameterLookup->get($serviceParameterId)
                        : null;

                    return [
                        'service_parameter_id' => $serviceParameterId ?: null,
                        'nama' => $item['parameter_name']
                            ?? $item['nama']
                            ?? $serviceParameter?->name
                            ?? '-',
                        'kategori' => $serviceParameter?->category?->name
                            ?? $item['kategori']
                            ?? '-',
                        'qty' => (int) ($item['qty'] ?? 0),
                        'harga' => array_key_exists('price', $item)
                            ? (float) $item['price']
                            : (float) ($serviceParameter?->price ?? 0),
                    ];
                })
                ->filter(fn ($item) => ($item['qty'] ?? 0) > 0)
                ->values();
            $penawaranAggregate = $this->aggregatePenawaran($permohonan);
            $penawaranItems = collect($penawaranAggregate['items'] ?? [])->values();

            $statusGlobal = $permohonan->status_global;
            if (! $statusGlobal && $permohonan->penjadwalan_sent_at) {
                $statusGlobal = 'penjadwalan';
            }
            if ($permohonan->spt_sent_at && in_array($statusGlobal, [null, 'penjadwalan'], true)) {
                $statusGlobal = 'pengujian';
            }
            if ($permohonan->pengujian?->sent_to_bap_at && in_array($statusGlobal, [null, 'penjadwalan', 'pengujian'], true)) {
                $statusGlobal = 'alur_bap';
            } elseif ($permohonan->pengujian && in_array($statusGlobal, [null, 'penjadwalan'], true)) {
                $statusGlobal = 'pengujian';
            }
            if ($permohonan->bap?->sent_to_user_at && ! $permohonan->bap?->user_approved_at
                && in_array($statusGlobal, [null, 'verifikasi', 'preparasi_analisa', 'prepanalisa'], true)) {
                $statusGlobal = 'alur_bap';
            }
            $statusGlobal = $statusGlobal ?? 'disposisi';
            $tahap = $this->mapStatusToTahap($statusGlobal);
            $kategori = $this->mapStatusToKategori($statusGlobal);
            $visibleDocs = $permohonan->dokumenPenawaran
                ->whereNotIn('status', ['draft']);
            $penawaranAccepted = $visibleDocs
                ->whereIn('status', ['received', 'signed'])
                ->isNotEmpty();
            $latestDoc = $visibleDocs
                ->sortByDesc('created_at')
                ->first();

            $dokumen = $visibleDocs->map(function ($doc) {
                $status = $doc->status ?? '';
                $keterangan = match ($status) {
                    'sent' => 'Dikirim',
                    'received' => 'Diterima',
                    'signed' => 'Ditandatangani',
                    'rejected' => 'Ditolak',
                    default => 'Tersedia',
                };
                $isCustomerUpload = in_array($status, ['received', 'signed'], true);

                $path = $doc->signed_file_path ?: $doc->file_path;
                $url = $path
                    ? route('penawaran.documents.show', [
                        'dokumen' => $doc,
                        'download' => $isCustomerUpload ? 0 : 1,
                    ])
                    : null;

                return [
                    'id' => $doc->id,
                    'nama' => $isCustomerUpload ? 'Dokumen Penawaran Disetujui' : 'Surat Penawaran',
                    'keterangan' => $isCustomerUpload ? 'Upload pelanggan' : $keterangan,
                    'terkirim' => true,
                    'jenis' => $isCustomerUpload ? 'upload_pelanggan' : 'unduh',
                    'status' => $status,
                    'url' => $url,
                ];
            })->values();

            if (! empty($company?->order_proof_path) && $this->fileExists($company->order_proof_path)) {
                $dokumen->prepend([
                    'id' => 'order-proof-'.$permohonan->id,
                    'nama' => 'Bukti Pemesanan Pelanggan',
                    'keterangan' => 'Upload pelanggan',
                    'terkirim' => true,
                    'jenis' => 'upload_pelanggan',
                    'status' => 'customer_upload',
                    'url' => route('riwayat_pelayanan.order-proof.show', $permohonan),
                ]);
            }
            $draft = $permohonan->draftLhu;
            $suratTagihanReady = ! empty($draft?->surat_tagihan_generated_at);
            $suratTagihanUrl = $suratTagihanReady ? route('riwayat_pelayanan.surat-tagihan.show', $permohonan) : null;
            $invoiceReady = ! empty($draft?->billing_verified_at) && ! empty($draft?->invoice_generated_at);
            $invoiceUrl = $invoiceReady ? route('riwayat_pelayanan.invoice.show', $permohonan) : null;
            $invoiceVerifiedAtRaw = $draft?->invoice_verified_by_user_at;
            $invoiceVerified = ! empty($invoiceVerifiedAtRaw);
            $billingUrl = (! empty($permohonan->draftLhu?->billing_sent_at) && ! empty($permohonan->draftLhu?->billing_file_path))
                ? route('riwayat_pelayanan.billing.show', $permohonan)
                : null;
            if ($suratTagihanUrl) {
                $dokumen->push([
                    'id' => 'surat-tagihan-'.$permohonan->id,
                    'nama' => 'Surat Tagihan',
                    'keterangan' => $invoiceVerified ? 'Sudah di-ACC pemohon' : 'Menunggu ACC surat tagihan',
                    'terkirim' => true,
                    'jenis' => 'unduh',
                    'status' => $invoiceVerified ? 'surat_tagihan_verified' : 'surat_tagihan_sent',
                    'url' => $suratTagihanUrl,
                ]);
            }
            if ($billingUrl) {
                $dokumen->push([
                    'id' => 'kode-billing-'.$permohonan->id,
                    'nama' => 'Kode Billing',
                    'keterangan' => ! empty($permohonan->draftLhu?->billing_paid_by_user_at)
                        ? 'Arsip Billing'
                        : 'Sudah dikirim ke pemohon',
                    'terkirim' => true,
                    'jenis' => 'unduh',
                    'status' => ! empty($permohonan->draftLhu?->billing_paid_by_user_at) ? 'billing_paid' : 'billing_sent',
                    'url' => $billingUrl,
                ]);
            }
            if ($invoiceUrl) {
                $dokumen->push([
                    'id' => 'invoice-'.$permohonan->id,
                    'nama' => 'Kuitansi',
                    'keterangan' => 'Pembayaran terverifikasi',
                    'terkirim' => true,
                    'jenis' => 'unduh',
                    'status' => 'kuitansi_ready',
                    'url' => $invoiceUrl,
                ]);
            }
            if (! empty($draft?->lhu_sent_to_user_at) && ! empty($draft?->suket_file_path) && $this->fileExists($draft->suket_file_path)) {
                $dokumen->push([
                    'id' => 'suket-'.$permohonan->id,
                    'nama' => 'Surat Keterangan',
                    'keterangan' => ! empty($draft?->lhu_user_approved_at)
                        ? 'Surat keterangan sudah tersedia'
                        : 'Terkirim bersama LHU',
                    'terkirim' => true,
                    'jenis' => 'unduh',
                    'status' => ! empty($draft?->lhu_user_approved_at) ? 'suket_ready' : 'lhu_sent',
                    'url' => route('riwayat_pelayanan.suket.show', $permohonan),
                ]);
            }
            if (! empty($draft?->lhu_user_approved_at) && ! empty($draft?->signed_file_path)) {
                $dokumen->push([
                    'id' => 'lhu-'.$permohonan->id,
                    'nama' => 'LHU',
                    'keterangan' => 'LHU sudah di acc',
                    'terkirim' => true,
                    'jenis' => 'unduh',
                    'status' => 'lhu_approved',
                    'url' => route('riwayat_pelayanan.lhu.show', $permohonan),
                ]);
            }

            $latestDocPath = $latestDoc?->signed_file_path ?: $latestDoc?->file_path;
            $latestDocUrl = ($latestDoc && $latestDocPath) ? route('penawaran.documents.show', $latestDoc) : null;
            $bap = $permohonan->bap;
            $bapStatusLabel = 'Belum mengirim BAP';
            if ($bap?->user_approved_at) {
                $bapStatusLabel = 'Sudah disetujui';
            } elseif ($bap?->sent_to_user_at) {
                $bapStatusLabel = 'Menunggu persetujuan';
            }
            [$workflowBadge, $workflowBadgeClass] = $this->mapWorkflowBadge($statusGlobal, $bap);
            if ($statusGlobal === 'verifikasi_pesanan') {
                if ($permohonan->order_review_status === 'pending_customer') {
                    $workflowBadge = 'Menunggu Persetujuan Perbaikan Pesanan';
                    $workflowBadgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                } else {
                    $workflowBadge = 'Sedang Diperiksa Admin';
                    $workflowBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
                }
            } elseif ($statusGlobal === 'disposisi' && $permohonan->order_review_status === 'approved') {
                $workflowBadge = 'Pesanan Diterima';
                $workflowBadgeClass = 'bg-success-subtle text-success border border-success-subtle';
            }
            $isBapApproved = (bool) $bap?->user_approved_at;
            $penawaranParameters = ($penawaranAccepted && $penawaranItems->isNotEmpty())
                ? $penawaranItems
                : $orderParameters;
            $verifiedOrderParameters = $orderParameters->values();
            $displayParameters = $penawaranParameters;
            if ($isBapApproved) {
                $pengujianAggregate = $this->aggregatePengujian(
                    $permohonan,
                    $penawaranItems->isNotEmpty()
                        ? $penawaranAggregate
                        : [
                            'items' => $orderParameters,
                            'subtotal' => 0,
                        ]
                );
                $latestParams = $pengujianAggregate['items'] ?? collect();
                if ($latestParams instanceof Collection && $latestParams->isNotEmpty()) {
                    $displayParameters = $latestParams->values();
                }
            }
            $assignments = $permohonan->assignments;
            $pcu = $assignments->where('role', 'pcu')->pluck('user.name')->filter()->values()->all();
            $ketuaTim = $assignments->where('role', 'pcu')->firstWhere('is_leader', true)
                ?? $assignments->where('role', 'pcu')->first();
            $ketuaTimNama = $ketuaTim?->user?->name ?? '-';
            $ketuaTimTtd = $this->signatureDataUrl($ketuaTim?->user?->signature_path);
            $penanggungTtd = ($bap?->user_approved_at && $company?->responsible_signature_path)
                ? route('permohonan.signature', $permohonan)
                : '';
            $penandatanganNama = $company?->authority_same
                ? ($company?->responsible_name ?? '-')
                : ($company?->authority_name ?? ($company?->responsible_name ?? '-'));
            $penandatanganJabatan = $company?->authority_same
                ? ($company?->authority_role ?? '-')
                : ($company?->authority_role ?? '-');
            $lokasiRows = collect();
            $pengujianParams = collect();
            if ($permohonan->pengujian) {
                $permohonan->pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use (&$lokasiRows, &$pengujianParams) {
                    $dokumenList = $lokasi->dokumen->sortBy('urutan')->map(function ($dokumen) use (&$pengujianParams) {
                        $params = $dokumen->parameters->sortBy('urutan')->map(function ($param) use (&$pengujianParams) {
                            $categoryName = $param->serviceParameter?->category?->name ?? '-';
                            $pengujianParams->push([
                                'service_parameter_id' => $param->service_parameter_id,
                                'nama' => $param->serviceParameter?->name ?? '-',
                                'kategori' => $categoryName,
                                'qty' => $param->qty,
                                'harga' => (float) ($param->serviceParameter?->price ?? 0),
                                'sesuai' => (bool) $param->is_sesuai,
                                'is_direct' => (bool) $param->is_direct,
                            ]);

                            return [
                                'id' => $param->id,
                                'nama' => $param->serviceParameter?->name ?? '-',
                                'kategori' => $categoryName,
                                'qty' => $param->qty,
                                'sesuai' => (bool) $param->is_sesuai,
                                'is_direct' => (bool) $param->is_direct,
                            ];
                        })->values();

                        return [
                            'nama' => $dokumen->label,
                            'parameter' => $params,
                        ];
                    })->values();

                    $lokasiRows->push([
                        'lokasi' => $lokasi->nama_lokasi,
                        'dokumen_list' => $dokumenList,
                    ]);
                });
            }
            $lokasiPengujian = $lokasiRows->pluck('lokasi')->filter()->implode(', ');
            $billingPath = $draft?->billing_file_path;
            $billingReady = ! empty($draft?->billing_sent_at) && ! empty($billingPath) && $this->fileExists($billingPath);
            $billingExpiresAtRaw = $draft?->billing_expires_at;
            $billingExpired = ! empty($billingExpiresAtRaw) && Carbon::parse($billingExpiresAtRaw)->isPast() && empty($draft?->billing_paid_by_user_at);
            $billingWaitingVerification = ! empty($draft?->billing_paid_by_user_at) && empty($draft?->billing_verified_at);
            if (in_array($statusGlobal, ['billing', 'kode_billing'], true)) {
                $tahap = $billingReady ? 'Kode Billing' : 'Surat Tagihan';
            }
            $billingStep = $permohonan->steps->first(function ($step) {
                return ($step->step->kode ?? null) === 'kode_billing';
            });
            $billingStepNote = trim((string) ($billingStep?->note ?? ''));
            $billingRenewalRequested = str_contains(strtolower($billingStepNote), 'kode billing terbaru');
            $canVerifyInvoice = $suratTagihanReady && ! $invoiceVerified;
            $scheduleAwaitingApproval = ! empty($permohonan->jadwal_sent_to_user_at)
                && empty($permohonan->jadwal_user_approved_at)
                && empty($permohonan->penjadwalan_sent_at);
            $lhuReady = ! empty($permohonan->draftLhu?->lhu_sent_to_user_at) && ! empty($permohonan->draftLhu?->signed_file_path);
            $suketReady = ! empty($draft?->lhu_sent_to_user_at) && ! empty($draft?->suket_file_path) && $this->fileExists($draft->suket_file_path);
            $ulasanSubmitted = isset($submittedUlasanLookup[(int) $permohonan->id]);
            $lhuNeedsUlasan = $lhuReady && ! $ulasanSubmitted;
            $lhuApproved = ! empty($permohonan->draftLhu?->lhu_user_approved_at);
            $lhuRevised = ! empty($permohonan->draftLhu?->lhu_user_revision_at);
            $kategoriResolved = $kategori;
            if ($statusGlobal === 'penyerahan_lhu' && ! $lhuApproved) {
                $kategoriResolved = 'ordered';
            }
            $canReorder = $kategoriResolved === 'completed' && $isBapApproved;

            return [
                'id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'lokasi' => $company?->company_city ?? '-',
                'tanggal' => optional($permohonan->created_at)->format('d M Y'),
                'status' => ($statusGlobal === 'penyerahan_lhu' && ! $lhuApproved)
                    ? 'Dalam Proses'
                    : $this->mapStatusLabel($statusGlobal),
                'workflow_badge' => $workflowBadge,
                'workflow_badge_class' => $workflowBadgeClass,
                'total' => $displayParameters->sum(fn ($p) => ($p['harga'] ?? 0) * ($p['qty'] ?? 1)),
                'tahap' => $tahap,
                'progress_tahap' => $this->mapStatusToProgressTahap($statusGlobal, $tahap),
                'kategori' => $kategoriResolved,
                'can_cancel' => $kategori === 'ordered'
                    && ! $penawaranAccepted
                    && in_array($statusGlobal, ['verifikasi_pesanan', 'disposisi', 'kaji_ulang', 'penawaran'], true),
                'order_review_status' => $permohonan->order_review_status,
                'order_review_note' => $permohonan->order_review_note,
                'order_review_sent_at' => optional($permohonan->order_review_sent_at)->format('d M Y H:i'),
                'order_review_can_approve' => $statusGlobal === 'verifikasi_pesanan'
                    && $permohonan->order_review_status === 'pending_customer',
                'order_review_has_comparison' => in_array($permohonan->order_review_status, ['pending_customer', 'approved'], true)
                    && ($originalReviewParameters->isNotEmpty() || $verifiedOrderParameters->isNotEmpty()),
                'order_review_original_parameters' => $originalReviewParameters,
                'order_review_final_parameters' => $verifiedOrderParameters,
                'order_review_approve_url' => $statusGlobal === 'verifikasi_pesanan'
                    && $permohonan->order_review_status === 'pending_customer'
                        ? route('riwayat_pelayanan.order-review.approve', $permohonan)
                        : null,
                'penawaran_doc_id' => $latestDoc?->id,
                'penawaran_doc_status' => $latestDoc?->status,
                'penawaran_doc_url' => $latestDocUrl,
                'penawaran_catatan' => $latestDoc?->catatan,
                'penanggung_jawab' => $company?->responsible_name ?? '-',
                'email' => $company?->company_email ?? '-',
                'telepon' => $company?->company_phone ?? '-',
                'alamat' => $company?->company_address ?? '-',
                'jenis_perusahaan' => $company?->company_type ?? '-',
                'provinsi' => $company?->company_province ?? '-',
                'kota' => $company?->company_city ?? '-',
                'jumlah_pekerja' => (int) ($company?->worker_count ?? 0),
                'cancel_reason' => $permohonan->cancel_reason,
                'cancel_note' => $permohonan->cancel_note,
                'cancelled_at' => optional($permohonan->cancelled_at)->format('d M Y H:i'),
                'cancelled_at_iso' => optional($permohonan->cancelled_at)->toIso8601String(),
                'dokumen' => $dokumen,
                'parameter' => $displayParameters,
                'bap_status_label' => $bapStatusLabel,
                'bap_can_approve' => (bool) ($bap?->sent_to_user_at) && ! $bap?->user_approved_at,
                'bap_doc_ready' => (bool) $bap?->sent_to_user_at,
                'bap_tanggal' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? optional($permohonan->created_at)->format('Y-m-d'),
                'bap_tanggal_mulai' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                'bap_tanggal_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d') ?? '-',
                'bap_lokasi' => $lokasiPengujian ?: ($permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-')),
                'bap_jenis_perusahaan' => $company?->company_type ?? '-',
                'bap_alamat' => $company?->company_address ?? '-',
                'bap_pcu' => $pcu,
                'bap_ketua_tim_nama' => $ketuaTimNama,
                'bap_ketua_tim_ttd' => $ketuaTimTtd,
                'bap_penanggung_ttd' => $penanggungTtd,
                'bap_penandatangan_nama' => $penandatanganNama,
                'bap_penandatangan_jabatan' => $penandatanganJabatan,
                'bap_lokasi_rows' => $lokasiRows->values(),
                'bap_parameter_order' => $penawaranParameters->map(fn ($p) => ['nama' => $p['nama'], 'qty' => $p['qty']])->values(),
                'bap_parameter_pengujian' => $pengujianParams->values(),
                'billing_sent' => ! empty($permohonan->draftLhu?->billing_sent_at),
                'billing_ready' => $billingReady,
                'billing_alert' => $billingReady && in_array($statusGlobal, ['kode_billing'], true),
                'billing_waiting_verification' => $billingWaitingVerification,
                'billing_sent_at' => optional($permohonan->draftLhu?->billing_sent_at)->format('d M Y H:i'),
                'billing_expires_at' => optional($billingExpiresAtRaw)->format('d M Y H:i'),
                'billing_expired' => $billingExpired,
                'billing_renewal_requested' => $billingRenewalRequested,
                'billing_renewal_note' => $billingStepNote,
                'billing_url' => $billingReady ? route('riwayat_pelayanan.billing.show', $permohonan) : null,
                'invoice_url' => $invoiceUrl ?: $suratTagihanUrl,
                'invoice_link_label' => $invoiceUrl ? 'Lihat Kuitansi' : 'Lihat Surat Tagihan',
                'invoice_verify_url' => $canVerifyInvoice ? route('riwayat_pelayanan.invoice.verify', $permohonan) : null,
                'invoice_verified_at' => optional($invoiceVerifiedAtRaw)->format('d M Y H:i'),
                'billing_renewal_request_url' => ($billingReady && $invoiceVerified && empty($permohonan->draftLhu?->billing_verified_at)) ? route('riwayat_pelayanan.billing.request-renewal', $permohonan) : null,
                'billing_confirm_url' => ($billingReady && $invoiceVerified) ? route('riwayat_pelayanan.billing.confirm-payment', $permohonan) : null,
                'billing_payment_proof_url' => (! empty($permohonan->draftLhu?->billing_payment_proof_path) && $this->fileExists($permohonan->draftLhu?->billing_payment_proof_path))
                    ? route('riwayat_pelayanan.billing.payment-proof.show', $permohonan)
                    : null,
                'billing_payment_proof_name' => $permohonan->draftLhu?->billing_payment_proof_name,
                'billing_payment_proof_uploaded_at' => optional($permohonan->draftLhu?->billing_payment_proof_uploaded_at)->format('d M Y H:i'),
                'billing_paid_by_user' => ! empty($permohonan->draftLhu?->billing_paid_by_user_at),
                'billing_paid_by_user_at' => optional($permohonan->draftLhu?->billing_paid_by_user_at)->format('d M Y H:i'),
                'billing_verified_at' => optional($permohonan->draftLhu?->billing_verified_at)->format('d M Y H:i'),
                'kuitansi_ready' => $invoiceReady,
                'kuitansi_pending' => ! empty($permohonan->draftLhu?->billing_verified_at) && ! $invoiceReady,
                'jadwal_sent_to_user_at' => optional($permohonan->jadwal_sent_to_user_at)->format('d M Y H:i'),
                'jadwal_user_approved_at' => optional($permohonan->jadwal_user_approved_at)->format('d M Y H:i'),
                'jadwal_mulai_label' => optional($permohonan->jadwal_mulai)->format('d M Y') ?? '-',
                'jadwal_selesai_label' => optional($permohonan->jadwal_selesai)->format('d M Y') ?? '-',
                'jadwal_pcu_count' => count($pcu),
                'jadwal_can_approve' => $scheduleAwaitingApproval,
                'jadwal_approve_url' => $scheduleAwaitingApproval ? route('riwayat_pelayanan.jadwal.approve', $permohonan) : null,
                'jadwal_reject_url' => $scheduleAwaitingApproval ? route('riwayat_pelayanan.jadwal.reject', $permohonan) : null,
                'lhu_ready' => $lhuReady,
                'lhu_url' => $lhuReady ? route('riwayat_pelayanan.lhu.show', $permohonan) : null,
                'suket_ready' => $suketReady,
                'suket_url' => $suketReady ? route('riwayat_pelayanan.suket.show', $permohonan) : null,
                'lhu_needs_ulasan' => $lhuNeedsUlasan,
                'lhu_ulasan_submit_url' => $lhuReady ? route('riwayat_pelayanan.lhu.ulasan.store', $permohonan) : null,
                'lhu_can_respond' => $lhuReady && ! $lhuApproved && ! $lhuRevised,
                'lhu_approve_url' => $lhuReady ? route('riwayat_pelayanan.lhu.approve', $permohonan) : null,
                'lhu_revise_url' => $lhuReady ? route('riwayat_pelayanan.lhu.revise', $permohonan) : null,
                'lhu_user_approved_at' => optional($permohonan->draftLhu?->lhu_user_approved_at)->format('d M Y H:i'),
                'lhu_user_revision_at' => optional($permohonan->draftLhu?->lhu_user_revision_at)->format('d M Y H:i'),
                'lhu_user_revision_note' => $permohonan->draftLhu?->lhu_user_revision_note,
                'lhu_badge' => $lhuApproved ? 'LHU disetujui' : ($lhuRevised ? 'Revisi diajukan' : ($lhuReady ? 'LHU tersedia' : null)),
                'can_reorder' => $canReorder,
                'reorder_url' => $canReorder ? route('riwayat_pelayanan.reorder', $permohonan) : null,
            ];
        });

        $tahapan = [
            'Verifikasi Pesanan',
            'Kaji Ulang',
            'Penawaran',
            'Penjadwalan',
            'Pengujian',
            'BAP',
            'Analisa',
            'LHU',
            'Surat Tagihan',
            'Kode Billing',
            'Kuitansi',
            'Penyerahan LHU',
        ];

        $colors = ['#2563eb', '#0d6efd', '#6f42c1', '#20c997', '#fd7e14', '#198754', '#0dcaf0', '#d63384', '#0891b2', '#6c757d', '#10b981', '#f59e0b'];

        return view('riwayat_pelayanan', [
            'riwayat' => $riwayat,
            'ulasanQuestions' => $ulasanQuestions,
            'tahapan' => $tahapan,
            'colors' => $colors,
            'paymentGuide' => $billingGuideService->getViewData(true),
        ]);
    }

    public function showBilling(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $path = $permohonan->draftLhu?->billing_file_path;
        if (! $path || ! $this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $name = $permohonan->draftLhu?->billing_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->download($fullPath, $name, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function showBillingPaymentProof(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $path = $permohonan->draftLhu?->billing_payment_proof_path;
        if (! $path || ! $this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $name = $permohonan->draftLhu?->billing_payment_proof_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true)) {
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="'.$name.'"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function showOrderProof(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $path = $permohonan->company?->order_proof_path;
        if (! $path || ! $this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($path);
        $name = basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="'.$name.'"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function showInvoice(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $permohonan->loadMissing([
            'company',
            'parameters',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'draftLhu',
        ]);

        if (! $permohonan->draftLhu?->billing_verified_at || ! $permohonan->draftLhu?->invoice_generated_at) {
            abort(403);
        }

        $penawaran = $this->aggregatePenawaran($permohonan);
        $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
        $company = $permohonan->company;
        $items = $pengujian['items'] instanceof Collection ? $pengujian['items']->values()->all() : [];
        $total = (float) ($pengujian['subtotal'] ?? 0);

        $filename = 'kuitansi-'.str_replace([' ', '/'], ['_', '-'], strtolower((string) ($permohonan->kode ?: $permohonan->id))).'.pdf';
        $pdf = Pdf::loadView('admin.invoice_preview', [
            'kode' => $permohonan->kode ?? '-',
            'tanggal' => now()->format('d-m-Y'),
            'perusahaan' => $company?->company_name ?? '-',
            'alamat' => $company?->company_address ?? '-',
            'items' => $items,
            'total' => $total,
            'document_title' => 'KUITANSI',
            'isPdf' => true,
            'logoDataUri' => $this->imageToDataUri(public_path('images/Logo Kemnaker.png')),
        ])->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.addslashes($filename).'"',
        ]);
    }

    public function showSuratTagihan(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $permohonan->loadMissing([
            'company',
            'parameters',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
            'draftLhu',
        ]);

        if (! $permohonan->draftLhu?->surat_tagihan_generated_at) {
            abort(403);
        }

        $penawaran = $this->aggregatePenawaran($permohonan);
        $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
        $company = $permohonan->company;
        $items = $pengujian['items'] instanceof Collection ? $pengujian['items']->values()->all() : [];
        $total = (float) ($pengujian['subtotal'] ?? 0);

        return view('admin.surat_tagihan_preview', [
            'kode' => $permohonan->kode ?? '-',
            'tanggal' => now()->format('d-m-Y'),
            'perusahaan' => $company?->company_name ?? '-',
            'alamat' => $company?->company_address ?? '-',
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function verifyInvoice(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $draft = $permohonan->draftLhu;
        if (! $draft || ! $draft->surat_tagihan_generated_at) {
            return response()->json(['message' => 'Surat tagihan belum tersedia.'], 422);
        }
        if (! empty($draft->invoice_verified_by_user_at)) {
            return response()->json(['message' => 'Surat tagihan sudah di-ACC.']);
        }

        $invoiceStep = WorkflowStep::firstWhere('kode', 'invoice');
        $billingStep = WorkflowStep::firstWhere('kode', 'kode_billing');

        DB::transaction(function () use ($permohonan, $draft, $invoiceStep, $billingStep) {
            $draft->update([
                'invoice_verified_by_user_id' => auth()->id(),
                'invoice_verified_by_user_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            if ($invoiceStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $invoiceStep->id],
                    [
                        'status' => 'approved',
                        'note' => 'Surat tagihan di-ACC pemohon',
                        'finished_at' => now(),
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            if ($billingStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $billingStep->id],
                    [
                        'status' => 'pending',
                        'note' => 'Surat tagihan di-ACC, menunggu upload kode billing',
                        'started_at' => now(),
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            $permohonan->update([
                'status_global' => 'billing',
                'status_lab' => 'billing',
            ]);
        });

        User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->select('id', 'role')
            ->get()
            ->each(function (User $user) use ($permohonan) {
                $url = $user->role === 'admin'
                    ? route('admin.billing.index')
                    : route('superadmin.billing.index');

                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Surat Tagihan Di-ACC Pemohon',
                    'message' => 'Surat tagihan permohonan '.$permohonan->kode.' telah di-ACC. Silakan lanjutkan upload kode billing.',
                    'url' => $url.'?kode='.urlencode((string) $permohonan->kode),
                ]);
            });

        return response()->json([
            'message' => 'Surat tagihan berhasil di-ACC. Tahap berikutnya: kode billing.',
        ]);
    }

    public function confirmBillingPayment(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $draft = $permohonan->draftLhu;
        if (! $draft || ! $draft->billing_sent_at) {
            return response()->json(['message' => 'Kode billing belum dikirim.'], 422);
        }
        if (empty($draft->invoice_verified_by_user_at)) {
            return response()->json(['message' => 'Surat tagihan belum di-ACC.'], 422);
        }
        if (! empty($draft->billing_paid_by_user_at)) {
            return response()->json(['message' => 'Konfirmasi pembayaran sudah dikirim.']);
        }

        $data = $request->validate([
            'payment_proof' => ['required', 'file', 'max:10240'],
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diupload.',
            'payment_proof.file' => 'File bukti pembayaran tidak valid.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 10 MB.',
        ]);

        $file = $data['payment_proof'];
        SafeDocumentUpload::validatePaymentProofOrFail($file, 'payment_proof');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $folder = 'billing-payment-proof/'.$permohonan->id;
        $filename = 'bukti_pembayaran_'.now()->format('Ymd_His').($ext !== '' ? '.'.$ext : '');
        $existingProofPath = $draft->billing_payment_proof_path;
        if ($existingProofPath) {
            $this->deleteIfExists($existingProofPath);
        }
        $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

        $draft->update([
            'billing_payment_proof_path' => $path,
            'billing_payment_proof_name' => $file->getClientOriginalName(),
            'billing_payment_proof_uploaded_by' => auth()->id(),
            'billing_payment_proof_uploaded_at' => now(),
            'billing_paid_by_user_id' => auth()->id(),
            'billing_paid_by_user_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->select('id')
            ->get()
            ->each(function (User $user) use ($permohonan) {
                $url = $user->role === 'admin'
                    ? route('admin.billing.index')
                    : route('superadmin.billing.index');
                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Konfirmasi Pembayaran Masuk',
                    'message' => 'Pemohon mengonfirmasi pembayaran untuk permohonan '.$permohonan->kode.' dan mengunggah bukti pembayaran.',
                    'url' => $url.'?kode='.urlencode((string) $permohonan->kode),
                ]);
            });

        return response()->json(['message' => 'Konfirmasi pembayaran berhasil dikirim.']);
    }

    public function requestBillingRenewal(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $draft = $permohonan->draftLhu;
        if (! $draft || ! $draft->billing_sent_at) {
            return response()->json(['message' => 'Kode billing belum dikirim.'], 422);
        }
        if (empty($draft->invoice_verified_by_user_at)) {
            return response()->json(['message' => 'Surat tagihan belum di-ACC.'], 422);
        }
        if (! empty($draft->billing_verified_at)) {
            return response()->json(['message' => 'Pembayaran sudah diverifikasi, tidak perlu ajukan ulang kode billing.'], 422);
        }

        $billingStep = WorkflowStep::firstWhere('kode', 'kode_billing');

        DB::transaction(function () use ($permohonan, $draft, $billingStep) {
            $this->deleteIfExists($draft->billing_file_path);
            $this->deleteIfExists($draft->billing_payment_proof_path);

            $draft->update([
                'billing_file_path' => null,
                'billing_file_name' => null,
                'billing_uploaded_by' => null,
                'billing_uploaded_at' => null,
                'billing_sent_by' => null,
                'billing_sent_at' => null,
                'billing_expires_at' => null,
                'billing_payment_proof_path' => null,
                'billing_payment_proof_name' => null,
                'billing_payment_proof_uploaded_by' => null,
                'billing_payment_proof_uploaded_at' => null,
                'billing_paid_by_user_id' => null,
                'billing_paid_by_user_at' => null,
                'billing_verified_by' => null,
                'billing_verified_at' => null,
                'updated_by' => auth()->id(),
            ]);

            if ($billingStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $billingStep->id],
                    [
                        'status' => 'pending',
                        'note' => 'Pemohon meminta kode billing terbaru, menunggu upload ulang kode billing',
                        'started_at' => now(),
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            $permohonan->update([
                'status_global' => 'billing',
                'status_lab' => 'billing',
            ]);
        });

        User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->select('id', 'role')
            ->get()
            ->each(function (User $user) use ($permohonan) {
                $url = $user->role === 'admin'
                    ? route('admin.billing.index')
                    : route('superadmin.billing.index');

                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Pengajuan Kode Billing Ulang',
                    'message' => 'Pemohon mengajukan kode billing terbaru untuk permohonan '.$permohonan->kode.'. Silakan upload ulang dokumen kode billing.',
                    'url' => $url.'?kode='.urlencode((string) $permohonan->kode),
                ]);
            });

        return response()->json([
            'message' => 'Pengajuan kode billing ulang berhasil dikirim ke admin.',
        ]);
    }

    public function showLhu(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }
        if ($this->needsLhuUlasan($permohonan)) {
            abort(403, 'Sebelum melihat LHU, isi formulir ulasan permohonan terlebih dahulu.');
        }

        $draft = $permohonan->draftLhu;
        $path = $draft?->signed_file_path;
        if (! $path || ! $draft?->lhu_sent_to_user_at || ! $this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $name = $draft?->signed_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$name.'"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function showSuket(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }
        if ($this->needsLhuUlasan($permohonan)) {
            abort(403, 'Sebelum melihat dokumen, isi formulir ulasan permohonan terlebih dahulu.');
        }

        $draft = $permohonan->draftLhu;
        $path = $draft?->suket_file_path;
        if (! $path || ! $draft?->lhu_sent_to_user_at || ! $this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $name = $draft?->suket_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$name.'"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function storeLhuUlasan(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $draft = $permohonan->draftLhu;
        if (! $draft || ! $draft->lhu_sent_to_user_at || empty($draft->signed_file_path)) {
            return response()->json(['message' => 'LHU belum tersedia untuk diulas.'], 422);
        }

        if ($this->hasSubmittedLhuUlasan($permohonan)) {
            return response()->json(['message' => 'Ulasan sudah pernah dikirim.']);
        }

        $questions = UlasanPermohonanQuestion::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'type', 'rating_labels']);

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'Pertanyaan ulasan belum tersedia.'], 422);
        }

        $answers = $request->input('answers');
        if (! is_array($answers)) {
            return response()->json([
                'message' => 'Jawaban ulasan tidak valid.',
                'errors' => ['answers' => ['Jawaban ulasan wajib diisi.']],
            ], 422);
        }

        $errors = [];
        $payloads = [];
        foreach ($questions as $question) {
            $rawAnswer = $answers[$question->id] ?? null;
            if ($question->type === 'rating') {
                $ratingValue = is_numeric($rawAnswer) ? (int) $rawAnswer : 0;
                $availableRatings = collect($question->rating_labels ?? [])
                    ->map(fn ($label, $index) => [
                        'value' => $index + 1,
                        'label' => trim((string) $label),
                    ])
                    ->filter(fn (array $option) => $option['label'] !== '')
                    ->pluck('value')
                    ->values();

                if ($availableRatings->isEmpty()) {
                    $availableRatings = collect([1, 2, 3, 4]);
                }

                if (! $availableRatings->contains($ratingValue)) {
                    $errors['answers.'.$question->id][] = 'Pilihan jawaban wajib diisi sesuai opsi yang tersedia.';

                    continue;
                }

                $payloads[] = [
                    'question_id' => $question->id,
                    'permohonan_id' => $permohonan->id,
                    'user_id' => auth()->id(),
                    'rating_value' => $ratingValue,
                    'text_answer' => null,
                ];

                continue;
            }

            $textAnswer = trim((string) ($rawAnswer ?? ''));
            if ($textAnswer === '') {
                $errors['answers.'.$question->id][] = 'Jawaban teks wajib diisi.';

                continue;
            }

            $payloads[] = [
                'question_id' => $question->id,
                'permohonan_id' => $permohonan->id,
                'user_id' => auth()->id(),
                'rating_value' => null,
                'text_answer' => $textAnswer,
            ];
        }

        if (! empty($errors)) {
            return response()->json([
                'message' => 'Jawaban ulasan belum lengkap.',
                'errors' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($payloads) {
            foreach ($payloads as $payload) {
                UlasanPermohonanResponse::create($payload);
            }
        });

        return response()->json(['message' => 'Ulasan berhasil dikirim.']);
    }

    public function approveLhu(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $draft = $permohonan->draftLhu;
        if (! $draft || ! $draft->lhu_sent_to_user_at) {
            return response()->json(['message' => 'LHU belum dikirim ke pemohon.'], 422);
        }
        if (! empty($draft->lhu_user_approved_at)) {
            return response()->json(['message' => 'LHU sudah disetujui.']);
        }

        $penyerahanStep = WorkflowStep::firstWhere('kode', 'penyerahan_lhu');
        DB::transaction(function () use ($permohonan, $draft, $penyerahanStep) {
            $draft->update([
                'lhu_user_approved_by' => auth()->id(),
                'lhu_user_approved_at' => now(),
                'lhu_user_revision_at' => null,
                'lhu_user_revision_note' => null,
                'updated_by' => auth()->id(),
            ]);

            if ($penyerahanStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $penyerahanStep->id],
                    [
                        'status' => 'approved',
                        'note' => 'LHU disetujui pemohon',
                        'finished_at' => now(),
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            $permohonan->update(['status_global' => 'penyerahan_lhu']);
        });

        return response()->json(['message' => 'LHU berhasil disetujui.']);
    }

    public function reviseLhu(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $draft = $permohonan->draftLhu;
        if (! $draft || ! $draft->lhu_sent_to_user_at) {
            return response()->json(['message' => 'LHU belum dikirim ke pemohon.'], 422);
        }

        $penyerahanStep = WorkflowStep::firstWhere('kode', 'penyerahan_lhu');

        DB::transaction(function () use ($permohonan, $draft, $penyerahanStep, $data) {
            $draft->update([
                'lhu_user_approved_by' => null,
                'lhu_user_approved_at' => null,
                'lhu_user_revision_at' => now(),
                'lhu_user_revision_note' => $data['note'],
                'updated_by' => auth()->id(),
            ]);

            if ($penyerahanStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $penyerahanStep->id],
                    [
                        'status' => 'in_progress',
                        'note' => 'Revisi pemohon: '.$data['note'],
                        'started_at' => now(),
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            $permohonan->update(['status_global' => 'penyerahan_lhu']);
        });

        User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->select('id')
            ->get()
            ->each(function (User $user) use ($permohonan) {
                $url = $user->role === 'admin'
                    ? route('admin.penyerahan-lhu.index')
                    : route('superadmin.penyerahan-lhu.index');
                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Revisi LHU dari Pemohon',
                    'message' => 'Pemohon mengajukan revisi LHU untuk permohonan '.$permohonan->kode.'. Silakan kirim ulang LHU.',
                    'url' => $url.'?kode='.urlencode((string) $permohonan->kode),
                ]);
            });

        return response()->json(['message' => 'Revisi LHU berhasil dikirim.']);
    }

    public function cancel(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($permohonan->status_global, ['verifikasi_pesanan', 'disposisi', 'kaji_ulang', 'penawaran'], true)) {
            return response()->json(['message' => 'Permohonan tidak bisa dibatalkan pada tahap ini.'], 422);
        }

        $hasAccepted = $permohonan->dokumenPenawaran
            ->whereIn('status', ['received', 'signed'])
            ->isNotEmpty();
        if ($hasAccepted) {
            return response()->json(['message' => 'Penawaran sudah disetujui dan tidak bisa dibatalkan.'], 422);
        }

        $data = request()->validate([
            'reason' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $permohonan->update([
            'status_global' => 'cancelled',
            'order_review_status' => $permohonan->status_global === 'verifikasi_pesanan'
                ? 'cancelled'
                : $permohonan->order_review_status,
            'cancel_reason' => $data['reason'],
            'cancel_note' => $data['note'] ?? null,
            'cancelled_at' => now(),
        ]);

        $permohonan->steps()
            ->whereIn('status', ['pending', 'in_progress'])
            ->update([
                'status' => 'cancelled',
                'note' => $data['note'] ?? null,
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        return response()->json(['success' => true]);
    }

    public function acceptPenawaran(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'dokumen_id' => ['required', 'integer'],
            'signed_document' => ['required', 'file', 'max:5120'],
        ]);
        SafeDocumentUpload::validateOrFail($data['signed_document'], 'signed_document');

        $doc = DokumenPenawaran::where('id', $data['dokumen_id'])
            ->where('permohonan_id', $permohonan->id)
            ->first();

        if (! $doc || $doc->status !== 'sent') {
            return response()->json(['message' => 'Dokumen penawaran tidak valid.'], 422);
        }

        $penawaranStep = WorkflowStep::where('kode', 'penawaran')->first();
        $penjadwalanStep = WorkflowStep::where('kode', 'penjadwalan')->first();
        if (! $penawaranStep || ! $penjadwalanStep) {
            return response()->json(['message' => 'Tahapan workflow belum lengkap.'], 422);
        }

        $hasApprovedParams = $permohonan->parameters()
            ->where('status', 'approved')
            ->where('qty', '>', 0)
            ->exists();
        $responseMessage = 'Penawaran disetujui, lanjut ke penjadwalan.';

        DB::transaction(function () use ($permohonan, $doc, $data, $penawaranStep, $penjadwalanStep, $hasApprovedParams, &$responseMessage) {
            $folder = 'penawaran/'.$permohonan->id;
            $ext = strtolower((string) $data['signed_document']->getClientOriginalExtension());
            $filename = 'penawaran_signed_'.now()->format('Ymd_His').'.'.($ext !== '' ? $ext : 'pdf');
            $path = $data['signed_document']->storeAs($folder, $filename, self::PRIVATE_DISK);

            $doc->update([
                'status' => 'received',
                'received_at' => now(),
                'signed_file_path' => $path,
            ]);

            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $penawaranStep->id)
                ->update([
                    'status' => 'approved',
                    'note' => 'Penawaran disetujui pemohon',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);

            if (! $hasApprovedParams) {
                $cancelNote = 'Semua parameter tidak bisa diuji. Permohonan ditutup otomatis setelah persetujuan dokumen.';

                PermohonanStep::where('permohonan_id', $permohonan->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update([
                        'status' => 'cancelled',
                        'note' => $cancelNote,
                        'updated_by' => auth()->id(),
                        'finished_at' => now(),
                    ]);

                $permohonan->update([
                    'status_global' => 'cancelled',
                    'cancel_reason' => 'Semua parameter tidak bisa diuji',
                    'cancel_note' => $cancelNote,
                    'cancelled_at' => now(),
                ]);

                $responseMessage = 'Tidak ada parameter yang bisa diuji. Pesanan otomatis dibatalkan.';

                return;
            }

            PermohonanStep::firstOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $penjadwalanStep->id],
                ['status' => 'pending', 'started_at' => now()]
            );

            $permohonan->update(['status_global' => 'penjadwalan']);
        });

        return response()->json(['message' => $responseMessage]);
    }

    public function rejectPenawaran(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'dokumen_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $doc = DokumenPenawaran::where('id', $data['dokumen_id'])
            ->where('permohonan_id', $permohonan->id)
            ->first();

        if (! $doc || $doc->status !== 'sent') {
            return response()->json(['message' => 'Dokumen penawaran tidak valid.'], 422);
        }

        $doc->update([
            'status' => 'rejected',
        ]);

        $rejectNote = $data['note'] ?? 'Penawaran ditolak pemohon';

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->update([
                'status' => 'cancelled',
                'note' => $rejectNote,
                'updated_by' => auth()->id(),
                'finished_at' => now(),
            ]);

        $permohonan->update([
            'status_global' => 'cancelled',
            'cancel_reason' => 'Penawaran ditolak',
            'cancel_note' => $rejectNote,
            'cancelled_at' => now(),
        ]);

        return response()->json(['message' => 'Penawaran ditolak.']);
    }

    public function approveBap(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'g-recaptcha-response' => ['required', 'captcha'],
        ]);

        $bap = $permohonan->bap;
        if (! $bap || ! $bap->sent_to_user_at) {
            return response()->json(['message' => 'BAP belum dikirim.'], 422);
        }

        if ($bap->user_approved_at) {
            return response()->json(['message' => 'BAP sudah disetujui.'], 422);
        }

        $bap->update([
            'user_approved_at' => now(),
            'user_approved_by' => auth()->id(),
            'admin_viewed_at' => null,
            'admin_viewed_by' => null,
            'status' => 'user_approved',
        ]);

        $bapStep = WorkflowStep::where('kode', 'alur_bap')->first();
        $verifPengujianStep = WorkflowStep::where('kode', 'verifikasi_pengujian')->first();
        if ($bapStep) {
            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $bapStep->id)
                ->update([
                    'status' => 'approved',
                    'note' => 'BAP disetujui pemohon',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
        }
        $labStatus = (string) ($permohonan->status_lab ?? '');
        if (in_array($labStatus, [
            'koding',
            'preparasi_analisa',
            'prepanalisa',
            'verifikasi',
            'pembuatan_lhu',
            'qc_lhu',
            'ttd_lhu',
            'lhu',
            'surat_tagihan',
            'invoice',
            'billing',
            'kode_billing',
            'penerbitan_suket',
            'penyerahan_lhu',
        ], true)) {
            $permohonan->update([
                'status_global' => $labStatus,
                'status_dokumen' => 'dokumen_selesai',
                'status_lab' => $labStatus,
            ]);

            return response()->json(['message' => 'BAP berhasil disetujui.']);
        }

        if ($verifPengujianStep) {
            PermohonanStep::firstOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $verifPengujianStep->id],
                ['status' => 'pending', 'started_at' => now()]
            );
        }

        $permohonan->update([
            'status_global' => 'verifikasi_pengujian',
            'status_dokumen' => 'verifikasi_pengujian',
            'status_lab' => 'menunggu_verifikasi_pengujian',
        ]);

        return response()->json(['message' => 'BAP berhasil disetujui.']);
    }

    public function approveSchedule(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $permohonan->jadwal_sent_to_user_at) {
            return response()->json(['message' => 'Jadwal belum dikirim ke pemohon.'], 422);
        }

        if ($permohonan->jadwal_user_approved_at) {
            return response()->json(['message' => 'Jadwal sudah disetujui.'], 422);
        }

        $penjadwalanStep = WorkflowStep::where('kode', 'penjadwalan')->first();

        DB::transaction(function () use ($permohonan, $penjadwalanStep) {
            $permohonan->update([
                'jadwal_user_approved_at' => now(),
                'jadwal_user_approved_by' => auth()->id(),
                'status_global' => 'penjadwalan',
            ]);

            if ($penjadwalanStep) {
                PermohonanStep::where('permohonan_id', $permohonan->id)
                    ->where('step_id', $penjadwalanStep->id)
                    ->update([
                        'status' => 'in_progress',
                        'note' => 'Jadwal disetujui pemohon, menunggu petugas meneruskan ke Approval MA',
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]);
            }

            User::query()
                ->whereIn('role', ['superadmin', 'penyelia'])
                ->select('id', 'role')
                ->get()
                ->each(function (User $user) use ($permohonan) {
                    $url = $user->role === 'penyelia'
                        ? route('penyelia.penjadwalan.index')
                        : route('superadmin.penjadwalan.index');

                    Notifikasi::create([
                        'user_id' => $user->id,
                        'title' => 'Jadwal Pengujian Disetujui Pemohon',
                        'message' => 'Pemohon telah menyetujui jadwal untuk permohonan '.$permohonan->kode.'. Silakan teruskan ke Approval MA.',
                        'url' => $url.'?kode='.urlencode((string) $permohonan->kode),
                    ]);
                });
        });

        return response()->json(['message' => 'Jadwal berhasil disetujui.']);
    }

    public function rejectSchedule(Request $request, Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $permohonan->jadwal_sent_to_user_at) {
            return response()->json(['message' => 'Jadwal belum dikirim ke pemohon.'], 422);
        }

        if ($permohonan->jadwal_user_approved_at) {
            return response()->json(['message' => 'Jadwal sudah disetujui, tidak bisa ditolak lagi.'], 422);
        }

        $data = $request->validate([
            'requested_start_date' => ['required', 'date'],
            'requested_end_date' => ['required', 'date', 'after_or_equal:requested_start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $penjadwalanStep = WorkflowStep::where('kode', 'penjadwalan')->first();
        $requestedStart = Carbon::parse($data['requested_start_date'])->format('d M Y');
        $requestedEnd = Carbon::parse($data['requested_end_date'])->format('d M Y');
        $reason = trim((string) ($data['reason'] ?? ''));
        $note = 'Dikembalikan ke penjadwalan dari user. Tanggal mulai pengujian yang diminta: '
            .$requestedStart.' s/d '.$requestedEnd
            .($reason !== '' ? '. Alasan: '.$reason : '.');

        DB::transaction(function () use ($permohonan, $penjadwalanStep, $note, $requestedStart, $requestedEnd, $reason) {
            $permohonan->update([
                'jadwal_sent_to_user_at' => null,
                'jadwal_user_approved_at' => null,
                'jadwal_user_approved_by' => null,
                'penjadwalan_sent_at' => null,
                'ma_approved_at' => null,
                'jadwal_mulai' => null,
                'jadwal_selesai' => null,
                'jadwal_catatan' => trim('Tanggal mulai pengujian yang diminta user: '
                    .$requestedStart
                    .' s/d '
                    .$requestedEnd
                    .($reason !== '' ? ' | Alasan: '.$reason : '')
                ),
                'status_global' => 'penjadwalan',
            ]);

            if ($penjadwalanStep) {
                PermohonanStep::where('permohonan_id', $permohonan->id)
                    ->where('step_id', $penjadwalanStep->id)
                    ->update([
                        'status' => 'in_progress',
                        'note' => $note,
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]);
            }

            User::query()
                ->whereIn('role', ['superadmin', 'penyelia'])
                ->select('id', 'role')
                ->get()
                ->each(function (User $user) use ($permohonan, $note) {
                    $url = $user->role === 'penyelia'
                        ? route('penyelia.penjadwalan.index')
                        : route('superadmin.penjadwalan.index');

                    Notifikasi::create([
                        'user_id' => $user->id,
                        'title' => 'Permintaan Ulang Tanggal Pengujian',
                        'message' => 'Pemohon meminta penjadwalan ulang untuk permohonan '.$permohonan->kode.'. '.$note,
                        'url' => $url,
                    ]);
                });
        });

        return response()->json(['message' => 'Tanggal mulai pengujian berhasil dikirim ke penjadwalan.']);
    }

    public function reorder(Permohonan $permohonan)
    {
        if ($permohonan->user_id !== auth()->id()) {
            abort(403);
        }

        $permohonan->loadMissing([
            'parameters',
            'bap',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
        ]);

        if (empty($permohonan->bap?->user_approved_at)) {
            return response()->json([
                'message' => 'Order kembali hanya tersedia setelah BAP disetujui pemohon.',
            ], 422);
        }

        $penawaran = $this->aggregatePenawaran($permohonan);
        $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
        $items = collect($pengujian['items'] ?? [])
            ->filter(function ($item) {
                return ! empty($item['service_parameter_id']) && (int) ($item['qty'] ?? 0) > 0;
            })
            ->values();

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Parameter setelah BAP ACC tidak tersedia untuk order kembali.',
            ], 422);
        }

        DB::transaction(function () use ($items) {
            $cart = Cart::firstOrCreate([
                'user_id' => auth()->id(),
                'status' => 'active',
            ]);

            $cart->items()->delete();

            $items->each(function ($item) use ($cart) {
                $cart->items()->create([
                    'service_parameter_id' => (int) $item['service_parameter_id'],
                    'qty' => (int) $item['qty'],
                    'price' => (float) ($item['harga'] ?? 0),
                ]);
            });
        });

        return response()->json([
            'message' => 'Keranjang berhasil diisi dari parameter setelah BAP ACC.',
            'redirect_url' => url('/keranjang'),
        ]);
    }

    private function normalizeCancelledNonTestableOrders(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $invalidPermohonans = Permohonan::query()
            ->where('user_id', $userId)
            ->where('status_global', 'penjadwalan')
            ->whereHas('steps', function ($query) {
                $query->whereIn('status', ['pending', 'in_progress'])
                    ->whereHas('step', function ($stepQuery) {
                        $stepQuery->where('kode', 'penjadwalan');
                    });
            })
            ->whereDoesntHave('parameters', function ($query) {
                $query->where('status', 'approved')
                    ->where('qty', '>', 0);
            })
            ->get();

        if ($invalidPermohonans->isEmpty()) {
            return;
        }

        foreach ($invalidPermohonans as $permohonan) {
            DB::transaction(function () use ($permohonan, $userId) {
                $cancelNote = 'Semua parameter tidak bisa diuji. Permohonan dibatalkan otomatis.';

                $permohonan->update([
                    'status_global' => 'cancelled',
                    'cancel_reason' => 'Semua parameter tidak bisa diuji',
                    'cancel_note' => $cancelNote,
                    'cancelled_at' => now(),
                    'jadwal_mulai' => null,
                    'jadwal_selesai' => null,
                    'penjadwalan_sent_at' => null,
                    'ma_approved_at' => null,
                ]);

                $permohonan->steps()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update([
                        'status' => 'cancelled',
                        'note' => $cancelNote,
                        'updated_by' => $userId,
                        'finished_at' => now(),
                    ]);
            });
        }
    }

    private function mapStatusToTahap(string $status): string
    {
        return match ($status) {
            'verifikasi_pesanan' => 'Verifikasi Pesanan',
            'disposisi' => 'Verifikasi Pesanan',
            'kaji_ulang' => 'Kaji Ulang',
            'penawaran' => 'Penawaran',
            'penjadwalan' => 'Penjadwalan',
            'pengujian' => 'Pengujian',
            'verifikasi_pengujian', 'verifikasi_pcu', 'alur_bap' => 'BAP',
            'koding', 'preparasi_analisa', 'verifikasi' => 'Analisa',
            'pembuatan_lhu', 'lhu', 'qc_lhu', 'ttd_lhu' => 'LHU',
            'surat_tagihan' => 'Surat Tagihan',
            'invoice' => 'Kuitansi',
            'billing', 'kode_billing' => 'Kode Billing',
            'penerbitan_suket' => 'Penyerahan LHU',
            'penyerahan_lhu' => 'Penyerahan LHU',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Kaji Ulang',
            default => 'Kaji Ulang',
        };
    }

    private function mapStatusToProgressTahap(string $status, string $tahap): string
    {
        return match ($status) {
            'disposisi' => 'Verifikasi Pesanan',
            default => $tahap,
        };
    }

    private function mapWorkflowBadge(string $status, $bap = null): array
    {
        return match ($status) {
            'verifikasi_pengujian' => ['Verifikasi Pengujian', 'bg-info-subtle text-info border border-info-subtle'],
            'verifikasi_pcu' => ['Verifikasi PCU', 'bg-info-subtle text-info border border-info-subtle'],
            'alur_bap' => [
                ! empty($bap?->sent_to_user_at) && empty($bap?->user_approved_at)
                    ? 'Menunggu Persetujuan BAP'
                    : 'BAP',
                'bg-warning-subtle text-warning border border-warning-subtle',
            ],
            default => [null, null],
        };
    }

    private function mapStatusLabel(string $status): string
    {
        return match ($status) {
            'rejected' => 'Ditolak',
            'penyerahan_lhu' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Dalam Proses',
        };
    }

    private function mapStatusToKategori(string $status): string
    {
        return match ($status) {
            'cancelled' => 'cancelled',
            'penyerahan_lhu' => 'completed',
            default => 'ordered',
        };
    }

    private function aggregatePenawaran(Permohonan $permohonan): array
    {
        $rows = $permohonan->parameters
            ->reject(function ($param) {
                return ($param->status ?? null) === 'rejected' || (int) ($param->qty ?? 0) <= 0;
            })
            ->map(function ($param) {
                return [
                    'service_parameter_id' => $param->service_parameter_id,
                    'nama' => $param->parameter_name ?? '-',
                    'kategori' => $param->serviceParameter?->category?->name ?? '-',
                    'qty' => (int) ($param->qty ?? 0),
                    'harga' => (float) ($param->price ?? 0),
                ];
            });

        $items = $this->aggregateRows($rows);
        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));

        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
    }

    private function aggregatePengujian(Permohonan $permohonan, array $penawaran): array
    {
        $penawaranMap = collect($penawaran['items'])
            ->mapWithKeys(fn ($item) => [$this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-') => $item]);

        $rows = collect();
        if ($permohonan->pengujian) {
            $permohonan->pengujian->lokasi->each(function ($lokasi) use (&$rows) {
                $lokasi->dokumen->each(function ($dokumen) use (&$rows) {
                    $dokumen->parameters->each(function ($param) use (&$rows) {
                        $rows->push([
                            'service_parameter_id' => $param->service_parameter_id,
                            'nama' => $param->serviceParameter?->name ?? '-',
                            'kategori' => $param->serviceParameter?->category?->name ?? '-',
                            'qty' => (int) ($param->qty ?? 0),
                            'harga' => (float) ($param->serviceParameter?->price ?? 0),
                        ]);
                    });
                });
            });
        }

        $items = $this->aggregateRows($rows, function ($row) use ($penawaranMap) {
            $key = $this->buildParamKey($row['service_parameter_id'] ?? null, $row['nama'] ?? '-');

            return (float) ($penawaranMap->get($key)['harga'] ?? ($row['harga'] ?? 0));
        });

        if ($items->isEmpty()) {
            $items = collect($penawaran['items']);
        }

        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));

        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
    }

    private function aggregateRows(Collection $rows, ?callable $resolvePrice = null): Collection
    {
        $grouped = [];
        foreach ($rows as $row) {
            $nama = trim((string) ($row['nama'] ?? '-'));
            $kategori = trim((string) ($row['kategori'] ?? '-'));
            $qty = (int) ($row['qty'] ?? 0);
            $harga = $resolvePrice ? (float) $resolvePrice($row) : (float) ($row['harga'] ?? 0);
            $id = $row['service_parameter_id'] ?? null;
            $key = $this->buildParamKey($id, $nama);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'service_parameter_id' => $id,
                    'nama' => $nama !== '' ? $nama : '-',
                    'kategori' => $kategori !== '' ? $kategori : '-',
                    'qty' => 0,
                    'harga' => $harga,
                ];
            }
            $grouped[$key]['qty'] += $qty;
            if ((($grouped[$key]['kategori'] ?? '-') === '-' || trim((string) ($grouped[$key]['kategori'] ?? '')) === '') && $kategori !== '') {
                $grouped[$key]['kategori'] = $kategori;
            }
            if ($grouped[$key]['harga'] <= 0 && $harga > 0) {
                $grouped[$key]['harga'] = $harga;
            }
        }

        return collect(array_values($grouped))->map(function ($item) {
            $item['subtotal'] = (float) (($item['qty'] ?? 0) * ($item['harga'] ?? 0));

            return $item;
        })->values();
    }

    private function buildParamKey($serviceParameterId, string $name): string
    {
        if (! empty($serviceParameterId)) {
            return 'id:'.$serviceParameterId;
        }

        return 'name:'.strtolower(trim($name));
    }

    private function needsLhuUlasan(Permohonan $permohonan): bool
    {
        if (! UlasanPermohonanQuestion::query()->where('is_active', true)->exists()) {
            return false;
        }

        return ! $this->hasSubmittedLhuUlasan($permohonan);
    }

    private function hasSubmittedLhuUlasan(Permohonan $permohonan): bool
    {
        return UlasanPermohonanResponse::query()
            ->where('permohonan_id', $permohonan->id)
            ->where('user_id', auth()->id())
            ->exists();
    }

    private function resolveDisk(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            return self::PRIVATE_DISK;
        }
        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            return self::LEGACY_DISK;
        }

        return null;
    }

    private function fileExists(?string $path): bool
    {
        return $this->resolveDisk($path) !== null;
    }

    private function deleteIfExists(?string $path): void
    {
        $disk = $this->resolveDisk($path);
        if ($disk) {
            Storage::disk($disk)->delete($path);
        }
    }

    private function signatureDataUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            return '';
        }

        $raw = Storage::disk($disk)->get($path);
        if ($raw === '' || $raw === null) {
            return '';
        }

        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }

    private function imageToDataUri(string $path): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
