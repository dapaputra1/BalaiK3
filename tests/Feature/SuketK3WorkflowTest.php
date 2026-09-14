<?php

namespace Tests\Feature;

use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\SuketK3;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuketK3WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_suket_menu_and_pages_for_roles()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = User::create([
            'name' => 'Admin Suket',
            'email' => 'admin.suket@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $pcu = User::create([
            'name' => 'Penguji PCU',
            'email' => 'pcu.suket@example.com',
            'password' => bcrypt('password'),
            'role' => 'pcu',
        ]);
        $qc = User::create([
            'name' => 'QC Suket',
            'email' => 'qc.suket@example.com',
            'password' => bcrypt('password'),
            'role' => 'qc',
        ]);
        $mp = User::create([
            'name' => 'Kepala Balai MP',
            'email' => 'mp.suket@example.com',
            'password' => bcrypt('password'),
            'role' => 'mp',
        ]);
        $regularUser = User::create([
            'name' => 'Pemohon PT Maju',
            'email' => 'pemohon@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // 1. User pemohon can access suket index and see form Tahap 1
        $userResponse = $this->actingAs($regularUser)->get('/suket-k3');
        $userResponse->assertStatus(200);
        $userResponse->assertSee('Tahap 1: Form Permohonan Suket K3 Lingkungan Kerja');

        // 2. Admin can access and see the standalone Suket K3 menu
        $response = $this->actingAs($admin)->get('/suket-k3');
        $response->assertStatus(200);
        $response->assertSee('Penerbitan Surat Keterangan (Suket) K3 Lingkungan Kerja');
        $response->assertSee('Alur Penerbitan Suket K3 Lingkungan Kerja (6 Tahap)');

        // 3. User pemohon submits permohonan dengan opsi faktor K3 & LHU auto
        $permohonan = Permohonan::query()->create([
            'kode' => 'PMH-TEST-001',
            'user_id' => $regularUser->id,
            'status_global' => 'selesai',
            'status_lab' => 'selesai',
        ]);
        DraftLhu::query()->create([
            'permohonan_id' => $permohonan->id,
            'signed_file_path' => 'draft_lhus/test_signed.pdf',
            'signed_file_name' => 'LHU_TTD_PMH-TEST-001.pdf',
            'created_by' => $admin->id,
        ]);

        $submitResponse = $this->actingAs($regularUser)->post('/suket-k3/store-by-order', [
            'nomor_order' => $permohonan->kode,
            'faktor_k3' => ['fisika', 'kimia'],
            'lhu_source' => 'auto',
            'catatan' => 'Uji test otomatis submit order dengan faktor K3',
        ]);
        $submitResponse->assertRedirect(route('suket.index'));

        $suket = SuketK3::where('nomor_order', $permohonan->kode)->latest('id')->first();
        $this->assertNotNull($suket);
        $this->assertEquals(1, $suket->status_tahap);
        $this->assertEquals(['fisika', 'kimia'], $suket->faktor_k3);
        $this->assertEquals('auto', $suket->lhu_source);
        $this->assertEquals('draft_lhus/test_signed.pdf', $suket->lhu_file_path);

        // 4. Penguji K3 (pcu) advances from Tahap 1 to Tahap 2 (Evaluasi Dokumen)
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Pemeriksaan kelengkapan dokumen LHU, foto, dan denah K3',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(2, $suket->status_tahap);

        // 5. Penguji K3 completes Tahap 2 (Evaluasi) to Tahap 3 (Penyusunan Suket)
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Dokumen evaluasi valid dan memenuhi standar Permenaker No. 5/2018',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);

        // 6. Test Auto-generate draft Suket template Permenaker 05/2018
        $draftResponse = $this->actingAs($pcu)->get("/suket-k3/{$suket->id}/generate-draft");
        $draftResponse->assertStatus(200);
        $draftResponse->assertHeader('Content-Type', 'application/msword; charset=UTF-8');

        $suket->refresh();
        $this->assertNotNull($suket->draft_file_path);

        // 7. Gerbang QC Review: QC approves draf suket (Tahap 3 -> Tahap 4)
        $qcResponse = $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'approve',
            'catatan' => 'Draf dokumen Suket telah diverifikasi QC dan disetujui.',
        ]);
        $qcResponse->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals('approved', $suket->qc_status);
        $this->assertEquals(4, $suket->status_tahap);

        // 8. Kepala Balai (mp) & Admin menandatangani / TTE di Tahap 4
        $this->actingAs($mp)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Tanda tangan elektronik Kepala Balai disetujui',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(5, $suket->status_tahap);
        $this->assertNotNull($suket->signed_at);

        // 9. Admin menerbitkan suket dengan nomor surat resmi di Tahap 5
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'nomor_surat' => '566/SK-LK/BK3-SBY/IX/2026',
            'catatan' => 'Surat keterangan K3 resmi diterbitkan',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(6, $suket->status_tahap);
        $this->assertEquals('566/SK-LK/BK3-SBY/IX/2026', $suket->nomor_surat);
        $this->assertNotNull($suket->published_at);

        // 10. Admin mengonfirmasi pengiriman ke pelanggan (Tahap 6)
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'resi_pengiriman' => 'JNE-K3-99887766',
            'metode_pengiriman' => 'Kurir Ekspedisi',
            'catatan' => 'Dokumen fisik dikirimkan ke alamat perusahaan pemohon',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertNotNull($suket->sent_to_customer_at);
        $this->assertEquals('JNE-K3-99887766', $suket->resi_pengiriman);
    }

    public function test_qc_rejection_and_stage_validations()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = User::create([
            'name' => 'Admin Test 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $pcu = User::create([
            'name' => 'PCU Test 2',
            'email' => 'pcu2@example.com',
            'password' => bcrypt('password'),
            'role' => 'pcu',
        ]);
        $qc = User::create([
            'name' => 'QC Test 2',
            'email' => 'qc2@example.com',
            'password' => bcrypt('password'),
            'role' => 'qc',
        ]);
        $regularUser = User::create([
            'name' => 'User Test 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $suket = SuketK3::create([
            'nomor_order' => 'ORD-VAL-999',
            'user_id' => $regularUser->id,
            'status_tahap' => 3,
            'qc_status' => 'pending',
            'faktor_k3' => ['ergonomi', 'psikologi'],
        ]);

        // A. User cannot advance internal stages
        $this->actingAs($regularUser)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
        ])->assertSessionHas('error');

        // B. Cannot advance to Tahap 4 without QC approval
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
        ])->assertSessionHas('error');

        // C. QC rejects draft with note
        $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'reject',
            'catatan' => 'Perbaiki klausul evaluasi ergonomi pada paragraf 2',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals('revision', $suket->qc_status);
        $this->assertEquals('Perbaiki klausul evaluasi ergonomi pada paragraf 2', $suket->qc_note);
        $this->assertEquals(3, $suket->status_tahap); // Remains in Tahap 3

        // D. After revision, QC approves
        $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'approve',
            'catatan' => 'Sudah direvisi dan sesuai',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals('approved', $suket->qc_status);
        $this->assertEquals(4, $suket->status_tahap);

        // E. Tahap 5 to 6 requires nomor_surat
        $suket->update(['status_tahap' => 5]);
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'nomor_surat' => '',
        ])->assertSessionHas('error');
    }
}
