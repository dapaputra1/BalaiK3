<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonans', 'status_dokumen')) {
                $table->string('status_dokumen')->nullable()->after('status_global');
            }
            if (!Schema::hasColumn('permohonans', 'status_lab')) {
                $table->string('status_lab')->nullable()->after('status_dokumen');
            }
        });

        $bapRows = DB::table('baps')
            ->select('permohonan_id', 'sent_to_user_at', 'user_approved_at')
            ->get()
            ->keyBy('permohonan_id');

        $revisionIds = DB::table('bap_items')
            ->join('baps', 'baps.id', '=', 'bap_items.bap_id')
            ->where('bap_items.review_status', 'revisi')
            ->pluck('baps.permohonan_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        DB::table('permohonans')
            ->select('id', 'status_global', 'status_dokumen', 'status_lab')
            ->orderBy('id')
            ->get()
            ->each(function ($permohonan) use ($bapRows, $revisionIds) {
                $statusGlobal = (string) ($permohonan->status_global ?? '');
                $bap = $bapRows->get($permohonan->id);
                $hasRevision = $revisionIds->has((int) $permohonan->id);

                $statusDokumen = $permohonan->status_dokumen ?: $this->resolveDocumentStatus($statusGlobal, $bap, $hasRevision);
                $statusLab = $permohonan->status_lab ?: $this->resolveLabStatus($statusGlobal, $statusDokumen);

                DB::table('permohonans')
                    ->where('id', $permohonan->id)
                    ->update([
                        'status_dokumen' => $statusDokumen,
                        'status_lab' => $statusLab,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'status_lab')) {
                $table->dropColumn('status_lab');
            }
            if (Schema::hasColumn('permohonans', 'status_dokumen')) {
                $table->dropColumn('status_dokumen');
            }
        });
    }

    private function resolveDocumentStatus(string $statusGlobal, object|null $bap, bool $hasRevision): ?string
    {
        if ($statusGlobal === 'cancelled') {
            return 'cancelled';
        }

        if ($hasRevision) {
            return 'verifikasi_pcu';
        }

        if (in_array($statusGlobal, [
            'koding',
            'preparasi_analisa',
            'prepanalisa',
            'verifikasi',
            'pembuatan_lhu',
            'qc_lhu',
            'ttd_lhu',
            'lhu',
            'invoice',
            'billing',
            'kode_billing',
            'penyerahan_lhu',
        ], true)) {
            return 'bap';
        }

        if ($statusGlobal === 'verifikasi_pengujian' || !empty($bap?->user_approved_at)) {
            return 'verifikasi_pengujian';
        }

        if (!empty($bap?->sent_to_user_at)) {
            return 'menunggu_persetujuan_bap';
        }

        if ($statusGlobal === 'alur_bap') {
            return 'bap';
        }

        if ($statusGlobal === 'pengujian') {
            return 'pengujian';
        }

        return null;
    }

    private function resolveLabStatus(string $statusGlobal, ?string $statusDokumen): ?string
    {
        if ($statusGlobal === 'cancelled') {
            return 'cancelled';
        }

        if (in_array($statusGlobal, [
            'koding',
            'preparasi_analisa',
            'prepanalisa',
            'verifikasi',
            'pembuatan_lhu',
            'qc_lhu',
            'ttd_lhu',
            'lhu',
            'invoice',
            'billing',
            'kode_billing',
            'penyerahan_lhu',
        ], true)) {
            return $statusGlobal;
        }

        if (in_array($statusDokumen, ['verifikasi_pengujian', 'verifikasi_pcu'], true)) {
            return 'menunggu_verifikasi_pengujian';
        }

        if (in_array($statusDokumen, ['pengujian', 'bap', 'menunggu_persetujuan_bap'], true)) {
            return 'pengujian';
        }

        return null;
    }
};
