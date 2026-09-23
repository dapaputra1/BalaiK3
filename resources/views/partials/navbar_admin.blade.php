@php
    $hasMaApprovalColumn = \Illuminate\Support\Facades\Schema::hasColumn('permohonans', 'ma_approved_at');
    $role = Auth::user()->role ?? 'guest';
    $roleLabelMap = [
        'superadmin' => 'Superadmin',
        'admin' => 'Administrator',
        'ma' => 'Manajemen Administrasi',
        'penyelia' => 'Penyelia',
        'pcu' => 'PCU',
        'analis' => 'Analis',
        'mp' => 'MP',
        'mt' => 'MT',
        'qc' => 'QC',
        'user' => 'User',
    ];
    $disposisiBadgeCount = 0;
    $orderReviewBadgeCount = \App\Models\Permohonan::query()
        ->whereIn('order_review_status', ['pending_admin', 'pending_customer'])
        ->whereNotIn('status_global', ['cancelled', 'rejected'])
        ->count();
    $kajiUlangBadgeCount = 0;
    $penawaranBadgeCount = 0;
    $penjadwalanBadgeCount = 0;
    $maApprovalBadgeCount = 0;
    $dokumenSptBadgeCount = 0;
    $pengujianBadgeCount = 0;
    $verifikasiPcuBadgeCount = 0;
    $bapBadgeCount = 0;
    $verifikasiPengujianBadgeCount = 0;
    $kodingBadgeCount = 0;
    $prepanalisaBadgeCount = 0;
    $verifikasiBadgeCount = 0;
    $draftLhuBadgeCount = 0;
    $qcLhuBadgeCount = 0;
    $ttdLhuBadgeCount = 0;
    $suratTagihanBadgeCount = 0;
    $invoiceBadgeCount = 0;
    $billingBadgeCount = 0;
    $suketBadgeCount = 0;
    $penyerahanLhuBadgeCount = 0;
    $stepsByKode = \App\Models\WorkflowStep::query()
        ->whereIn('kode', ['disposisi', 'kaji_ulang', 'penawaran', 'penjadwalan'])
        ->get()
        ->keyBy('kode');
    $disposisiStep = $stepsByKode->get('disposisi');
    $kajiUlangStep = $stepsByKode->get('kaji_ulang');
    $penawaranStep = $stepsByKode->get('penawaran');
    $penjadwalanStep = $stepsByKode->get('penjadwalan');

    if ($disposisiStep) {
        $baseDisposisiQuery = \App\Models\Permohonan::query()
            ->whereHas('steps', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            });

        $countPendingMp = (clone $baseDisposisiQuery)
            ->whereHas('approvals', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('role', 'mp')
                    ->where('status', 'pending');
            })
            ->count();

        $countPendingMt = (clone $baseDisposisiQuery)
            ->whereHas('approvals', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('role', 'mt')
                    ->where('status', 'pending');
            })
            ->whereHas('approvals', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('role', 'mp')
                    ->where('status', 'approved');
            })
            ->count();

        if ($role === 'mp') {
            $disposisiBadgeCount = $countPendingMp;
        } elseif ($role === 'mt') {
            $disposisiBadgeCount = $countPendingMt;
        } else {
            $disposisiBadgeCount = $countPendingMp + $countPendingMt;
        }
    }

    if ($kajiUlangStep) {
        $kajiUlangBadgeCount = \App\Models\Permohonan::query()
            ->whereHas('steps', function ($query) use ($kajiUlangStep) {
                $query->where('step_id', $kajiUlangStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })
            ->count();
    }

    if ($penawaranStep) {
        $penawaranBadgeCount = \App\Models\Permohonan::query()
            ->whereHas('steps', function ($query) use ($penawaranStep) {
                $query->where('step_id', $penawaranStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })
            ->count();
    }

    if ($penjadwalanStep) {
        $penjadwalanBadgeCount = \App\Models\Permohonan::query()
            ->whereHas('steps', function ($query) use ($penjadwalanStep) {
                $query->where('step_id', $penjadwalanStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })
            ->whereNull('penjadwalan_sent_at')
            ->count();
    }

    if ($hasMaApprovalColumn) {
        $maApprovalBadgeCount = \App\Models\Permohonan::query()
            ->whereNotNull('jadwal_mulai')
            ->whereNotNull('jadwal_selesai')
            ->whereNotNull('penjadwalan_sent_at')
            ->whereNull('ma_approved_at')
            ->whereNull('spt_sent_at')
            ->count();

        $dokumenSptBadgeCount = \App\Models\Permohonan::query()
            ->whereNotNull('jadwal_mulai')
            ->whereNotNull('jadwal_selesai')
            ->whereNotNull('penjadwalan_sent_at')
            ->whereNotNull('ma_approved_at')
            ->whereNull('spt_sent_at')
            ->count();
    } else {
        $dokumenSptBadgeCount = \App\Models\Permohonan::query()
            ->whereNotNull('jadwal_mulai')
            ->whereNotNull('jadwal_selesai')
            ->whereNotNull('penjadwalan_sent_at')
            ->whereNull('spt_sent_at')
            ->count();
    }

    $pengujianBadgeCount = \App\Models\Permohonan::query()
        ->whereNotNull('spt_sent_at')
        ->where(function ($query) {
            $query->whereNull('status_dokumen')
                ->orWhere('status_dokumen', 'pengujian');
        })
        ->count();

    $verifikasiPcuBadgeCount = \App\Models\Permohonan::query()
        ->whereNotNull('spt_sent_at')
        ->whereIn('status_dokumen', ['verifikasi_pcu', 'siap_bap'])
        ->count();

    $bapBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'alur_bap')
        ->whereIn('status_dokumen', ['bap', 'menunggu_persetujuan_bap'])
        ->where(function ($query) {
            $query->whereDoesntHave('bap')
                ->orWhereHas('bap', function ($bapQuery) {
                    $bapQuery->whereNull('user_approved_at');
                });
        })
        ->count();

    $draftLhuStep = \App\Models\WorkflowStep::where('kode', 'pembuatan_lhu')->first();

    if ($role === 'pcu' && auth()->check()) {
        $userId = auth()->id();

        if ($penjadwalanStep) {
            $penjadwalanBadgeCount = \App\Models\Permohonan::query()
                ->whereHas('steps', function ($query) use ($penjadwalanStep) {
                    $query->where('step_id', $penjadwalanStep->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                })
                ->whereHas('assignments', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->where('role', 'pcu');
                })
                ->whereNull('penjadwalan_sent_at')
                ->count();
        }

        $pengujianBadgeCount = \App\Models\Permohonan::query()
            ->whereNotNull('spt_sent_at')
            ->whereHas('assignments', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('role', 'pcu');
            })
            ->where(function ($query) {
                $query->whereNull('status_dokumen')
                    ->orWhere('status_dokumen', 'pengujian');
            })
            ->count();

        $verifikasiPcuBadgeCount = \App\Models\Permohonan::query()
            ->whereNotNull('spt_sent_at')
            ->whereHas('assignments', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('role', 'pcu');
            })
            ->whereIn('status_dokumen', ['verifikasi_pcu', 'siap_bap'])
            ->count();

        $bapBadgeCount = \App\Models\Permohonan::query()
            ->whereHas('assignments', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('role', 'pcu');
            })
            ->where('status_global', 'alur_bap')
            ->whereIn('status_dokumen', ['bap', 'menunggu_persetujuan_bap'])
            ->where(function ($query) {
                $query->whereDoesntHave('bap')
                    ->orWhereHas('bap', function ($bapQuery) {
                        $bapQuery->whereNull('user_approved_at');
                    });
            })
            ->count();

        if ($draftLhuStep) {
            $draftLhuBadgeCount = \App\Models\Permohonan::query()
                ->whereHas('assignments', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->where('role', 'pcu');
                })
                ->where(function ($query) use ($draftLhuStep) {
                    $query->whereHas('steps', function ($q) use ($draftLhuStep) {
                        $q->where('step_id', $draftLhuStep->id)
                            ->whereIn('status', ['pending', 'in_progress']);
                    })->orWhere('status_global', 'pembuatan_lhu')
                        ->orWhere(function ($extra) {
                            $extra->whereIn('status_global', ['preparasi_analisa', 'verifikasi'])
                                ->whereHas('pengujian.lokasi.dokumen.parameters', function ($paramQuery) {
                                    $paramQuery->where('is_direct', true);
                                });
                        });
                })
                ->count();
        }
    }

    $verifikasiPengujianBadgeCount = \App\Models\Permohonan::query()
        ->where('status_dokumen', 'verifikasi_pengujian')
        ->count();

    $kodingBadgeCount = \App\Models\Permohonan::query()
        ->where('status_lab', 'koding')
        ->count();

    $prepanalisaStep = \App\Models\WorkflowStep::where('kode', 'preparasi_analisa')->first();
    if ($prepanalisaStep) {
        $prepanalisaBadgeCount = \App\Models\Permohonan::query()
            ->whereHas('steps', function ($query) use ($prepanalisaStep) {
                $query->where('step_id', $prepanalisaStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })
            ->count();
    }

    $verifikasiStep = \App\Models\WorkflowStep::where('kode', 'verifikasi')->first();
    if ($verifikasiStep) {
        $verifikasiBadgeCount = \App\Models\Permohonan::query()
            ->where('status_global', 'verifikasi')
            ->count();
    }

    if ($draftLhuStep) {
        $draftLhuBadgeCount = \App\Models\Permohonan::query()
            ->where(function ($query) use ($draftLhuStep) {
                $query->whereHas('steps', function ($q) use ($draftLhuStep) {
                    $q->where('step_id', $draftLhuStep->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                })->orWhere('status_global', 'pembuatan_lhu')
                    ->orWhere(function ($extra) {
                        $extra->whereIn('status_global', ['preparasi_analisa', 'verifikasi'])
                            ->whereHas('pengujian.lokasi.dokumen.parameters', function ($paramQuery) {
                                $paramQuery->where('is_direct', true);
                            });
                    });
            })
            ->count();
    }

    $qcLhuBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'qc_lhu')
        ->count();

    $ttdLhuBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'ttd_lhu')
        ->count();

    $suratTagihanBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'surat_tagihan')
        ->count();

    $invoiceBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'invoice')
        ->count();

    $billingBadgeCount = \App\Models\Permohonan::query()
        ->whereIn('status_global', ['billing', 'kode_billing'])
        ->where(function ($query) {
            $query->whereDoesntHave('draftLhu')
                ->orWhereHas('draftLhu', function ($draftQuery) {
                    $draftQuery->whereNull('billing_verified_at');
                });
        })
        ->count();

    $suketBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'penerbitan_suket')
        ->count();

    $penyerahanLhuBadgeCount = \App\Models\Permohonan::query()
        ->where('status_global', 'penyerahan_lhu')
        ->where(function ($query) {
            $query->whereDoesntHave('draftLhu')
                ->orWhereHas('draftLhu', function ($draftQuery) {
                    $draftQuery->whereNull('lhu_user_approved_at');
                });
        })
        ->count();

    $badgeCounts = [
        'feedback' => \App\Models\Feedback::whereDoesntHave('reply')->count(),
        'order_review' => $orderReviewBadgeCount,
        'disposisi' => $disposisiBadgeCount,
        'kaji_ulang' => $kajiUlangBadgeCount,
        'penawaran' => $penawaranBadgeCount,
        'penjadwalan' => $penjadwalanBadgeCount,
        'approval_ma' => $maApprovalBadgeCount,
        'dokumen_spt' => $dokumenSptBadgeCount,
        'pengujian' => $pengujianBadgeCount,
        'verifikasi_pcu' => $verifikasiPcuBadgeCount,
        'bap' => $bapBadgeCount,
        'verifikasi_pengujian' => $verifikasiPengujianBadgeCount,
        'koding' => $kodingBadgeCount,
        'prepanalisa' => $prepanalisaBadgeCount,
        'verifikasi' => $verifikasiBadgeCount,
        'draft_lhu' => $draftLhuBadgeCount,
        'qc_lhu' => $qcLhuBadgeCount,
        'ttd_lhu' => $ttdLhuBadgeCount,
        'surat_tagihan' => $suratTagihanBadgeCount,
        'invoice' => $invoiceBadgeCount,
        'billing' => $billingBadgeCount,
        'suket' => $suketBadgeCount,
        'suket_k3' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', '<', 9)->count() : 0,
        'suket_t2' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 2)->count() : 0,
        'suket_t3' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 3)->count() : 0,
        'suket_qc' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 3)->where('qc_status', 'pending')->count() : 0,
        'suket_t4' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 4)->count() : 0,
        'suket_t5' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 5)->count() : 0,
        'suket_t6' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 6)->count() : 0,
        'suket_t7' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 7)->count() : 0,
        'suket_t8' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 8)->count() : 0,
        'suket_t9' => \Illuminate\Support\Facades\Schema::hasTable('suket_k3s') ? \App\Models\SuketK3::where('status_tahap', 9)->count() : 0,
        'penyerahan_lhu' => $penyerahanLhuBadgeCount,
    ];
    $menus = [
        'superadmin' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'superadmin.dashboard'],
            ['type' => 'link', 'label' => 'Kelola Petugas', 'icon' => 'bi bi-person-gear', 'route' => 'superadmin.petugas.index'],
            ['type' => 'link', 'label' => 'Kelola Pengguna', 'icon' => 'bi bi-people', 'route' => 'superadmin.users.index'],
            ['type' => 'link', 'label' => 'Kelola Berita', 'icon' => 'bi bi-newspaper', 'route' => 'superadmin.berita.index'],
            ['type' => 'link', 'label' => 'Kelola Jejaring', 'icon' => 'bi bi-diagram-3', 'route' => 'superadmin.jejaring.index'],
            ['type' => 'link', 'label' => 'Kelola Medsos', 'icon' => 'bi bi-share', 'route' => 'superadmin.medsos.index'],
            ['type' => 'link', 'label' => 'Kelola Gambar Aplikasi', 'icon' => 'bi bi-image', 'route' => 'superadmin.login-backgrounds.index'],
            ['type' => 'link', 'label' => 'Kelola Parameter', 'icon' => 'bi bi-tags', 'route' => 'superadmin.service-parameters.index'],
            ['type' => 'link', 'label' => 'Kelola LOD', 'icon' => 'bi bi-sliders2', 'route' => 'superadmin.parameter-lods.index'],
            ['type' => 'link', 'label' => 'Kelola Rumus', 'icon' => 'bi bi-activity', 'route' => 'superadmin.absorbansi.index'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            ['type' => 'link', 'label' => 'Ulasan Permohonan', 'icon' => 'bi bi-clipboard-heart', 'route' => 'superadmin.ulasan-permohonan.index'],
            ['type' => 'link', 'label' => 'Feedback', 'icon' => 'bi bi-chat-dots', 'route' => 'superadmin.feedback.index', 'badge' => $badgeCounts['feedback'] ?? 0],
            ['type' => 'link', 'label' => 'Pengujian Ergonomi (SNI 9011)', 'icon' => 'bi bi-activity', 'route' => 'ergo.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'id' => 'alurKerja',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Verifikasi Pesanan', 'icon' => 'bi bi-card-checklist', 'route' => 'superadmin.order-review.index', 'badge' => $badgeCounts['order_review'] ?? 0],
                    ['label' => 'Disposisi', 'icon' => 'bi bi-arrow-repeat', 'href' => 'disposisi', 'badge' => $badgeCounts['disposisi'] ?? 0],
                    ['label' => 'Kaji Ulang', 'icon' => 'bi bi-search', 'href' => 'kajiulang', 'badge' => $badgeCounts['kaji_ulang'] ?? 0],
                    ['label' => 'Penawaran', 'icon' => 'bi bi-file-earmark-text', 'href' => 'penawaran', 'badge' => $badgeCounts['penawaran'] ?? 0],
                    ['label' => 'Penjadwalan', 'icon' => 'bi bi-calendar-event', 'href' => 'penjadwalan', 'badge' => $badgeCounts['penjadwalan'] ?? 0],
                    ['label' => 'Approval MA', 'icon' => 'bi bi-patch-check', 'href' => route('superadmin.approval-ma.index'), 'badge' => $badgeCounts['approval_ma'] ?? 0],
                    ['label' => 'Dokumen SPT', 'icon' => 'bi bi-file-earmark-ruled', 'href' => route('superadmin.dokumen-spt.index'), 'badge' => $badgeCounts['dokumen_spt'] ?? 0],
                    ['label' => 'Pengujian', 'icon' => 'bi bi-clipboard-data', 'href' => route('superadmin.pengujian.index'), 'badge' => $badgeCounts['pengujian'] ?? 0],
                    ['label' => 'Berita Acara Pengujian', 'icon' => 'bi bi-file-earmark-check', 'href' => 'bap', 'badge' => $badgeCounts['bap'] ?? 0],
                    ['label' => 'Verifikasi Pengujian', 'icon' => 'bi bi-clipboard-check', 'href' => route('superadmin.verifikasi-pengujian.index'), 'badge' => $badgeCounts['verifikasi_pengujian'] ?? 0],
                    ['label' => 'Verifikasi PCU', 'icon' => 'bi bi-clipboard-check', 'href' => route('superadmin.verifikasi-pcu.index'), 'badge' => $badgeCounts['verifikasi_pcu'] ?? 0],
                    ['label' => 'Koding', 'icon' => 'bi bi-code-slash', 'href' => 'koding', 'badge' => $badgeCounts['koding'] ?? 0],
                    ['label' => 'Preparasi Analisa', 'icon' => 'bi bi-clipboard-check', 'href' => route('superadmin.prepanalisa.index'), 'badge' => $badgeCounts['prepanalisa'] ?? 0],
                    ['label' => 'Verifikasi Hasil Analis', 'icon' => 'bi bi-shield-check', 'href' => route('superadmin.verifikasi.index'), 'badge' => $badgeCounts['verifikasi'] ?? 0],
                    ['label' => 'Draft LHU', 'icon' => 'bi bi-file-earmark-text', 'href' => route('superadmin.draft-lhu.index'), 'badge' => $badgeCounts['draft_lhu'] ?? 0],
                    ['label' => 'QC LHU', 'icon' => 'bi bi-clipboard-check', 'href' => route('superadmin.qc-lhu.index'), 'badge' => $badgeCounts['qc_lhu'] ?? 0],
                    ['label' => 'Penandatanganan LHU', 'icon' => 'bi bi-pen', 'href' => route('superadmin.ttd-lhu.index'), 'badge' => $badgeCounts['ttd_lhu'] ?? 0],
                    ['label' => 'Surat Tagihan', 'icon' => 'bi bi-envelope-paper', 'href' => route('superadmin.surat-tagihan.index'), 'badge' => $badgeCounts['surat_tagihan'] ?? 0],
                    ['label' => 'Kode Billing', 'icon' => 'bi bi-upc', 'href' => route('superadmin.billing.index'), 'badge' => $badgeCounts['billing'] ?? 0],
                    ['label' => 'Kuitansi', 'icon' => 'bi bi-receipt', 'href' => route('superadmin.invoice.index'), 'badge' => $badgeCounts['invoice'] ?? 0],
                    ['label' => 'Penyerahan LHU', 'icon' => 'bi bi-send', 'href' => route('superadmin.penyerahan-lhu.index'), 'badge' => $badgeCounts['penyerahan_lhu'] ?? 0],
                ],
            ],
            [
                'type' => 'section',
                'label' => 'Penerbitan Suket',
                'id' => 'suketSuperadmin',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Semua Permohonan', 'icon' => 'bi bi-grid-fill', 'href' => route('suket.index'), 'badge' => $badgeCounts['suket_k3'] ?? 0],
                    ['label' => 'Evaluasi Dokumen', 'icon' => 'bi bi-file-earmark-check', 'href' => route('suket.index', ['stage' => 2]), 'badge' => $badgeCounts['suket_t2'] ?? 0],
                    ['label' => 'Penyusunan Suket', 'icon' => 'bi bi-file-earmark-word', 'href' => route('suket.index', ['stage' => 3]), 'badge' => $badgeCounts['suket_t3'] ?? 0],
                    ['label' => 'Review QC Suket', 'icon' => 'bi bi-shield-check', 'href' => route('suket.index', ['stage' => 'qc']), 'badge' => $badgeCounts['suket_qc'] ?? 0],
                    ['label' => 'Penandatanganan Suket', 'icon' => 'bi bi-pen', 'href' => route('suket.index', ['stage' => 4]), 'badge' => $badgeCounts['suket_t4'] ?? 0],
                    ['label' => 'Penerbitan Suket', 'icon' => 'bi bi-award', 'href' => route('suket.index', ['stage' => 5]), 'badge' => $badgeCounts['suket_t5'] ?? 0],
                    ['label' => 'Surat Tagihan Suket', 'icon' => 'bi bi-envelope-paper', 'href' => route('suket.index', ['stage' => 6]), 'badge' => $badgeCounts['suket_t6'] ?? 0],
                    ['label' => 'Kode Billing Suket', 'icon' => 'bi bi-upc', 'href' => route('suket.index', ['stage' => 7]), 'badge' => $badgeCounts['suket_t7'] ?? 0],
                    ['label' => 'Kuitansi Suket', 'icon' => 'bi bi-receipt', 'href' => route('suket.index', ['stage' => 8]), 'badge' => $badgeCounts['suket_t8'] ?? 0],
                    ['label' => 'Penyerahan Suket', 'icon' => 'bi bi-send-check', 'href' => route('suket.index', ['stage' => 9]), 'badge' => $badgeCounts['suket_t9'] ?? 0],
                ],
            ],
        ],
        'admin' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'admin.dashboard'],
            ['type' => 'link', 'label' => 'Kelola Berita', 'icon' => 'bi bi-newspaper', 'route' => 'superadmin.berita.index'],
            ['type' => 'link', 'label' => 'Kelola Jejaring', 'icon' => 'bi bi-diagram-3', 'route' => 'superadmin.jejaring.index'],
            ['type' => 'link', 'label' => 'Kelola Medsos', 'icon' => 'bi bi-share', 'route' => 'superadmin.medsos.index'],
            ['type' => 'link', 'label' => 'Kelola Gambar Aplikasi', 'icon' => 'bi bi-image', 'route' => 'superadmin.login-backgrounds.index'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            ['type' => 'link', 'label' => 'Ulasan Permohonan', 'icon' => 'bi bi-clipboard-heart', 'route' => 'admin.ulasan-permohonan.index'],
            ['type' => 'link', 'label' => 'Feedback', 'icon' => 'bi bi-chat-dots', 'route' => 'superadmin.feedback.index', 'badge' => $badgeCounts['feedback'] ?? 0],
            ['type' => 'link', 'label' => 'Pengujian Ergonomi (SNI 9011)', 'icon' => 'bi bi-activity', 'route' => 'ergo.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'id' => 'alurKerjaAdmin',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Verifikasi Pesanan', 'icon' => 'bi bi-card-checklist', 'route' => 'superadmin.order-review.index', 'badge' => $badgeCounts['order_review'] ?? 0],
                    ['label' => 'Penawaran', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.penawaran.index', 'badge' => $badgeCounts['penawaran'] ?? 0],
                    ['label' => 'Dokumen SPT', 'icon' => 'bi bi-file-earmark-ruled', 'route' => 'admin.dokumen-spt.index', 'badge' => $badgeCounts['dokumen_spt'] ?? 0],
                    ['label' => 'BAP', 'icon' => 'bi bi-file-earmark-check', 'route' => 'admin.bap.index', 'badge' => $badgeCounts['bap'] ?? 0],
                    ['label' => 'Koding ID Lokasi', 'icon' => 'bi bi-geo-alt', 'route' => 'admin.koding.index', 'badge' => $badgeCounts['koding'] ?? 0],
                    ['label' => 'Draft LHU', 'icon' => 'bi bi-file-earmark-text', 'route' => 'admin.draft-lhu.index', 'badge' => $badgeCounts['draft_lhu'] ?? 0],
                    ['label' => 'Surat Tagihan', 'icon' => 'bi bi-envelope-paper', 'route' => 'admin.surat-tagihan.index', 'badge' => $badgeCounts['surat_tagihan'] ?? 0],
                    ['label' => 'Kode Billing', 'icon' => 'bi bi-upc', 'route' => 'admin.billing.index', 'badge' => $badgeCounts['billing'] ?? 0],
                    ['label' => 'Kuitansi', 'icon' => 'bi bi-receipt', 'route' => 'admin.invoice.index', 'badge' => $badgeCounts['invoice'] ?? 0],
                    ['label' => 'Penyerahan LHU', 'icon' => 'bi bi-send', 'route' => 'admin.penyerahan-lhu.index', 'badge' => $badgeCounts['penyerahan_lhu'] ?? 0],
                ],
            ],
            [
                'type' => 'section',
                'label' => 'Penerbitan Suket',
                'id' => 'suketAdmin',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Semua Permohonan', 'icon' => 'bi bi-grid-fill', 'href' => route('suket.index'), 'badge' => $badgeCounts['suket_k3'] ?? 0],
                    ['label' => 'Evaluasi Dokumen', 'icon' => 'bi bi-file-earmark-check', 'href' => route('suket.index', ['stage' => 2]), 'badge' => $badgeCounts['suket_t2'] ?? 0],
                    ['label' => 'Penandatanganan Suket', 'icon' => 'bi bi-pen', 'href' => route('suket.index', ['stage' => 4]), 'badge' => $badgeCounts['suket_t4'] ?? 0],
                    ['label' => 'Penerbitan Suket', 'icon' => 'bi bi-award', 'href' => route('suket.index', ['stage' => 5]), 'badge' => $badgeCounts['suket_t5'] ?? 0],
                    ['label' => 'Surat Tagihan Suket', 'icon' => 'bi bi-envelope-paper', 'href' => route('suket.index', ['stage' => 6]), 'badge' => $badgeCounts['suket_t6'] ?? 0],
                    ['label' => 'Kode Billing Suket', 'icon' => 'bi bi-upc', 'href' => route('suket.index', ['stage' => 7]), 'badge' => $badgeCounts['suket_t7'] ?? 0],
                    ['label' => 'Kuitansi Suket', 'icon' => 'bi bi-receipt', 'href' => route('suket.index', ['stage' => 8]), 'badge' => $badgeCounts['suket_t8'] ?? 0],
                    ['label' => 'Penyerahan Suket', 'icon' => 'bi bi-send-check', 'href' => route('suket.index', ['stage' => 9]), 'badge' => $badgeCounts['suket_t9'] ?? 0],
                ],
            ],
        ],
        'ma' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'ma.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Approval MA', 'icon' => 'bi bi-patch-check', 'route' => 'ma.approval-ma.index', 'badge' => $badgeCounts['approval_ma'] ?? 0],
                ],
            ],
        ],
        'mp' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'superadmin.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Disposisi', 'icon' => 'bi bi-arrow-repeat', 'route' => 'superadmin.disposisi.index', 'badge' => $badgeCounts['disposisi'] ?? 0],
                ],
            ],
            [
                'type' => 'section',
                'label' => 'Penerbitan Suket',
                'id' => 'suketMp',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Penandatanganan Suket', 'icon' => 'bi bi-pen', 'href' => route('suket.index', ['stage' => 4]), 'badge' => $badgeCounts['suket_t4'] ?? 0],
                ],
            ],
        ],
        'mt' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'superadmin.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Disposisi', 'icon' => 'bi bi-arrow-repeat', 'route' => 'superadmin.disposisi.index', 'badge' => $badgeCounts['disposisi'] ?? 0],
                    ['label' => 'Kaji Ulang', 'icon' => 'bi bi-search', 'route' => 'superadmin.kajiulang.index', 'badge' => $badgeCounts['kaji_ulang'] ?? 0],
                    ['label' => 'Penandatanganan LHU', 'icon' => 'bi bi-pen', 'route' => 'superadmin.ttd-lhu.index', 'badge' => $badgeCounts['ttd_lhu'] ?? 0],
                ],
            ],
        ],
        'penyelia' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'penyelia.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Penjadwalan', 'icon' => 'bi bi-calendar-event', 'route' => 'penyelia.penjadwalan.index', 'badge' => $badgeCounts['penjadwalan'] ?? 0],
                    ['label' => 'Verifikasi Pengujian', 'icon' => 'bi bi-clipboard-check', 'route' => 'penyelia.verifikasi-pengujian.index', 'badge' => $badgeCounts['verifikasi_pengujian'] ?? 0],
                ],
            ],
        ],
        'pcu' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'pcu.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Penjadwalan', 'icon' => 'bi bi-calendar-event', 'route' => 'pcu.penjadwalan.index', 'badge' => $badgeCounts['penjadwalan'] ?? 0],
                    ['label' => 'Pengujian', 'icon' => 'bi bi-clipboard-data', 'route' => 'pcu.pengujian.index', 'badge' => $badgeCounts['pengujian'] ?? 0],
                    ['label' => 'Verifikasi PCU', 'icon' => 'bi bi-clipboard-check', 'route' => 'pcu.verifikasi-pcu.index', 'badge' => $badgeCounts['verifikasi_pcu'] ?? 0],
                    ['label' => 'BAP', 'icon' => 'bi bi-file-earmark-check', 'route' => 'pcu.bap.index', 'badge' => $badgeCounts['bap'] ?? 0],
                    ['label' => 'Draft LHU', 'icon' => 'bi bi-file-earmark-text', 'route' => 'pcu.draft-lhu.index', 'badge' => $badgeCounts['draft_lhu'] ?? 0],
                ],
            ],
            [
                'type' => 'section',
                'label' => 'Penerbitan Suket',
                'id' => 'suketPcu',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Evaluasi Dokumen', 'icon' => 'bi bi-file-earmark-check', 'href' => route('suket.index', ['stage' => 2]), 'badge' => $badgeCounts['suket_t2'] ?? 0],
                    ['label' => 'Penyusunan Suket', 'icon' => 'bi bi-file-earmark-word', 'href' => route('suket.index', ['stage' => 3]), 'badge' => $badgeCounts['suket_t3'] ?? 0],
                ],
            ],
        ],
        'analis' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'analis.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Preparasi Analisa', 'icon' => 'bi bi-clipboard-check', 'route' => 'analis.prepanalisa.index', 'badge' => $badgeCounts['prepanalisa'] ?? 0],
                ],
            ],
        ],
        'qc' => [
            ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'bi bi-grid', 'route' => 'qc.dashboard'],
            ['type' => 'link', 'label' => 'Permohonan', 'icon' => 'bi bi-file-earmark-text', 'route' => 'superadmin.permohonan.index'],
            [
                'type' => 'section',
                'label' => 'Alur Kerja',
                'children' => [
                    ['label' => 'Verifikasi Hasil Analisa', 'icon' => 'bi bi-shield-check', 'route' => 'qc.verifikasi.index', 'badge' => $badgeCounts['verifikasi'] ?? 0],
                    ['label' => 'QC LHU', 'icon' => 'bi bi-clipboard-check', 'route' => 'qc.qc-lhu.index', 'badge' => $badgeCounts['qc_lhu'] ?? 0],
                ],
            ],
            [
                'type' => 'section',
                'label' => 'Penerbitan Suket',
                'id' => 'suketQc',
                'collapsible' => true,
                'children' => [
                    ['label' => 'Review QC Suket', 'icon' => 'bi bi-shield-check', 'href' => route('suket.index', ['stage' => 'qc']), 'badge' => $badgeCounts['suket_qc'] ?? 0],
                ],
            ],
        ],
    ];

    $items = $menus[$role] ?? $menus['admin'];
@endphp
<style>
      
    .sidebar {
        width:260px;
        min-height:100vh;
        background:#0f3b63;
        position:fixed;
        left:0; top:0;
        transition: transform .34s cubic-bezier(.22,.61,.36,1), width .28s cubic-bezier(.22,.61,.36,1);
        will-change: transform, width;
    }

    .sidebar.hidden {
        transform: translateX(-100%);
    }
      
    .sidebar.collapsed {
        width:80px;
    }

    /* Sembunyikan teks */
    .sidebar.collapsed .menu .menu-text,
    .sidebar.collapsed .brand-text,
    .sidebar.collapsed .menu-title {
        display:none;
    }

    /* Center icon ketika collapsed */
    .sidebar.collapsed .menu a {
        justify-content:center;
        padding:12px 0;
    }

    /* Icon tetap proporsional */
    .sidebar.collapsed .menu i {
        font-size:18px;
    }

    /* Submenu hidden total saat collapsed */
    .sidebar.collapsed .submenu {
        display:none !important;
    }

    /* Brand logo tetap center */
    .sidebar.collapsed .brand {
        justify-content:center;
    }

    .sidebar.collapsed .brand img {
        margin:0;
    }

    .brand {
        padding:18px 20px;
        color:#fff;
        display:flex;
        align-items:center;
        gap:10px;
        border-bottom:1px solid rgba(255,255,255,.12);
    }
    .brand img { width:34px; }
    .brand-text { line-height:1.2; }

    .menu {
        padding:14px;
        height: calc(100vh - 80px);
        overflow-y: auto;
        scrollbar-width: thin;
    }
    
    /* Custom scrollbar */
    .menu::-webkit-scrollbar {
        width:6px;
    }
    .menu::-webkit-scrollbar-track {
        background: transparent;
    }
    .menu::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.35);
        border-radius:10px;
    }
    .menu::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,.55);
    }
    .menu-title {
        color:#9ec3e6;
        font-size:11px;
        text-transform:uppercase;
        margin:14px 0 6px;
    }
    .menu a {
        display:flex;
        align-items:center;
        gap:10px;
        padding:10px 14px;
        color:#e6eef7;
        text-decoration:none;
        border-radius:10px;
        font-weight:500;
        margin-bottom:6px;
        position: relative;
        transition: background .2s ease, color .2s ease;
    }
    .menu a.active {
        background: transparent;
        color:#fff;
    }
    .menu a:hover,
    .menu a.active:hover {
        background:#fff !important;
        color:#0f3b63 !important;
    }
    .menu-text {
        display:inline-block;
        transition: transform .15s ease;
    }
    .menu i {
        transition: transform .15s ease;
    }
    .menu a.active .menu-text {
        transform: scale(1.08);
        transform-origin: left center;
        text-decoration: underline;
        text-decoration-color: #fff;
        text-decoration-thickness: 2px;
        text-underline-offset: 4px;
    }
    .menu a:hover .menu-text,
    .menu a.active:hover .menu-text {
        color: #0f3b63 !important;
        text-decoration-color: #0f3b63 !important;
    }
    .menu a:hover i,
    .menu a.active i {
        transform: scale(1.08);
        color: #0f3b63 !important;
    }
    .menu i { font-size:16px; }
    .menu-icon {
        position: relative;
        display: inline-flex;
    }
    .menu-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: #fff;
        border-radius: 999px;
        font-size: 10px;
        line-height: 16px;
        min-width: 16px;
        text-align: center;
        padding: 0 4px;
    }
    .submenu-badge {
        top: -8px;
        right: -8px;
    }

    .submenu a { padding-left:38px; font-size:13px; }

    .main {
        margin-left:260px;
        width: calc(100% - 260px);
        max-width: calc(100% - 260px);
        min-width: 0;
        overflow-x: hidden;
        transition: margin-left .34s cubic-bezier(.22,.61,.36,1);
        will-change: margin-left;
    }
    .main.collapsed {
        margin-left:80px;
        width: calc(100% - 80px);
        max-width: calc(100% - 80px);
    }
    .main.expanded {
        margin-left:0;
        width: 100%;
        max-width: 100%;
    }

    .topbar {
        background:#fff;
        padding:12px 20px;
        min-height: var(--admin-topbar-height);
        border-bottom:1px solid #eee;
        display:flex;
        justify-content:space-between;
        align-items:center;
        position: fixed;
        top: 0;
        left: 260px;
        right: 0;
        z-index: 1100;
        box-shadow: 0 10px 24px rgba(15, 59, 99, 0.08);
        backdrop-filter: blur(10px);
        width: auto;
        max-width: none;
        overflow: visible;
    }

    .main.collapsed .topbar {
        left: 80px;
    }

    .main.expanded .topbar {
        left: 0;
    }
    .topbar i {
        font-size:20px;
        color:#0f3b63;
        cursor:pointer;
        margin-left:18px;
    }

    @media (max-width: 991.98px) {
        .main,
        .main.collapsed,
        .main.expanded {
            margin-left: 0;
            width: 100%;
            max-width: 100%;
        }

        .topbar,
        .main.collapsed .topbar,
        .main.expanded .topbar {
            left: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .sidebar,
        .main {
            transition: none !important;
        }
    }

    .menu-toggle {
    display:flex;
    align-items:center;
    justify-content:space-between;
    }

    .dropdown-icon {
        font-size:14px;
        transition:transform .25s ease;
    }

    /* saat terbuka → panah ke bawah */
    .menu-toggle[aria-expanded="true"] .dropdown-icon {
        transform: rotate(90deg);
    }

    /* sidebar collapse */
    .sidebar.collapsed .dropdown-icon {
        display:none;
    }

    </style>



<div class="sidebar" id="sidebar">
    <div class="brand">
        <img src="{{ asset('images/Logo.png') }}" alt="Logo">
        <div class="brand-text">
            <div class="fw-semibold">Balai K3 Surabaya</div>
            <small>{{ $roleLabelMap[$role] ?? ucfirst($role) }}</small>
        </div>
    </div>

    <div class="menu">
        @foreach($items as $item)
            @if($item['type'] === 'link')
                @php
                    $href = $item['href'] ?? ($item['route'] ?? null ? route($item['route']) : '#');
                    $isActive = isset($item['route']) ? request()->routeIs($item['route']) : false;
                @endphp
                <a href="{{ $href }}" class="{{ $isActive ? 'active' : '' }}">
                    <span class="menu-icon">
                        <i class="{{ $item['icon'] }}"></i>
                        @if(!empty($item['badge']))
                            <span class="menu-badge">{{ $item['badge'] }}</span>
                        @endif
                    </span>
                    <span class="menu-text">{{ $item['label'] }}</span>
                </a>
            @elseif($item['type'] === 'section')
                @php
                    $resolveChild = function ($child) {
                        $href = $child['href'] ?? ($child['route'] ?? null ? route($child['route']) : '#');
                        $isActive = false;

                        if (isset($child['route'])) {
                            $isActive = request()->routeIs($child['route']);
                        } elseif (isset($child['href'])) {
                            $hrefValue = $child['href'];
                            $absolute = url($hrefValue);
                            
                            $reqParsed = parse_url(request()->fullUrl());
                            $targetParsed = parse_url($absolute);

                            $reqPath = trim($reqParsed['path'] ?? '', '/');
                            $targetPath = trim($targetParsed['path'] ?? '', '/');

                            parse_str($reqParsed['query'] ?? '', $reqQuery);
                            parse_str($targetParsed['query'] ?? '', $targetQuery);

                            if ($reqPath !== '' && ($reqPath === $targetPath || request()->is($targetPath . '/*'))) {
                                if (!empty($targetQuery)) {
                                    $isActive = true;
                                    foreach ($targetQuery as $k => $v) {
                                        if (!isset($reqQuery[$k]) || (string)$reqQuery[$k] !== (string)$v) {
                                            $isActive = false;
                                            break;
                                        }
                                    }
                                } else {
                                    // Target URL tanpa query string (seperti Semua Permohonan -> /suket-k3)
                                    // Hanya aktif jika request TIDAK memiliki parameter 'stage'
                                    $isActive = empty($reqQuery['stage']);
                                }
                            }

                            if (!$isActive && empty($targetQuery) && empty($reqQuery['stage'])) {
                                $isActive = request()->url() === $absolute || request()->fullUrlIs($absolute);
                            }

                            if (!$isActive && empty($targetQuery) && empty($reqQuery['stage'])) {
                                if ($targetPath !== '' && $targetPath !== 'suket-k3') {
                                    $isActive = request()->is($targetPath) || request()->is($targetPath . '/*');
                                }
                            }

                            if (
                                !$isActive &&
                                empty($targetQuery) &&
                                empty($reqQuery['stage']) &&
                                is_string($hrefValue) &&
                                !str_contains($hrefValue, '/') &&
                                !str_starts_with($hrefValue, 'http')
                            ) {
                                $isActive = request()->is('*' . $hrefValue . '*');
                            }
                        }

                        return [$href, $isActive];
                    };

                    $isSectionActive = false;
                    $resolvedChildren = [];
                    foreach ($item['children'] as $child) {
                        [$childHref, $isChildActive] = $resolveChild($child);
                        $resolvedChildren[] = $child + ['_href' => $childHref, '_active' => $isChildActive];
                        if ($isChildActive) {
                            $isSectionActive = true;
                        }
                    }
                @endphp
                <div class="menu-title">{{ $item['label'] }}</div>
                @if(!empty($item['collapsible']))
                    <a class="menu-toggle {{ $isSectionActive ? '' : 'collapsed' }}" data-bs-toggle="collapse" data-bs-target="#{{ $item['id'] }}" href="#{{ $item['id'] }}" role="button" aria-expanded="{{ $isSectionActive ? 'true' : 'false' }}" aria-controls="{{ $item['id'] }}">
                        <div class="menu-label">
                            <i class="bi bi-diagram-3"></i>
                            <span class="menu-text">{{ $item['label'] }}</span>
                        </div>
                        <i class="bi bi-chevron-right dropdown-icon"></i>
                    </a>
                    <div class="collapse submenu {{ $isSectionActive ? 'show' : '' }}" id="{{ $item['id'] }}">
                        @foreach($resolvedChildren as $child)
                            <a href="{{ $child['_href'] ?? '#' }}" class="{{ !empty($child['_active']) ? 'active' : '' }}">
                                <span class="menu-icon">
                                    <i class="{{ $child['icon'] }}"></i>
                                    @if(!empty($child['badge']))
                                        <span class="menu-badge submenu-badge">{{ $child['badge'] }}</span>
                                    @endif
                                </span>
                                <span class="menu-text">{{ $child['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    @foreach($resolvedChildren as $child)
                        <a href="{{ $child['_href'] ?? '#' }}" class="{{ !empty($child['_active']) ? 'active' : '' }}">
                            <span class="menu-icon">
                                <i class="{{ $child['icon'] }}"></i>
                                @if(!empty($child['badge']))
                                    <span class="menu-badge">{{ $child['badge'] }}</span>
                                @endif
                            </span>
                            <span class="menu-text">{{ $child['label'] }}</span>
                        </a>
                    @endforeach
                @endif
            @endif
        @endforeach
    </div>
</div>

<script>
    (() => {
        const menu = document.querySelector('#sidebar .menu');
        if (!menu) return;

        const key = 'adminSidebarScrollTop';
        const saved = sessionStorage.getItem(key);
        if (saved !== null) {
            const value = parseInt(saved, 10);
            if (!Number.isNaN(value)) {
                menu.scrollTop = value;
            }
        } else {
            menu.scrollTop = 0;
        }

        const saveScroll = () => {
            sessionStorage.setItem(key, String(menu.scrollTop));
        };

        menu.addEventListener('click', (event) => {
            const toggle = event.target.closest('.menu-toggle');
            if (toggle) {
                event.preventDefault();
                const targetSelector = toggle.getAttribute('data-bs-target') || toggle.getAttribute('href');
                if (targetSelector) {
                    const targetEl = document.querySelector(targetSelector);
                    if (targetEl && window.bootstrap && window.bootstrap.Collapse) {
                        const bsCollapse = window.bootstrap.Collapse.getOrCreateInstance(targetEl);
                        bsCollapse.toggle();
                    }
                }
                return;
            }

            const link = event.target.closest('a[href]');
            if (!link) return;
            saveScroll();
        });
    })();
</script>
