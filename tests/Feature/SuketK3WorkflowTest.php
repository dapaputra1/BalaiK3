<?php

namespace Tests\Feature;

use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\SuketK3;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $otherUser = User::create([
            'name' => 'Pemohon Lain',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // 1. Regular user accessing internal /suket-k3 gets redirected to /permohonan-suket
        $redirectResponse = $this->actingAs($regularUser)->get('/suket-k3');
        $redirectResponse->assertRedirect(route('user.suket.index'));

        // 2. User pemohon accesses /permohonan-suket directly and sees their Tahap 1 form
        $userPortalResponse = $this->actingAs($regularUser)->get('/permohonan-suket');
        $userPortalResponse->assertStatus(200);
        $userPortalResponse->assertSee('Permohonan Surat Keterangan (Suket) K3 Lingkungan Kerja');

        // 3. Admin accesses /suket-k3: starts from Tahap 2, NO Tahap 1 form
        $adminResponse = $this->actingAs($admin)->get('/suket-k3');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Penerbitan Surat Keterangan (Suket) K3 Lingkungan Kerja');
        $adminResponse->assertSee('Tahap 2');
        $adminResponse->assertDontSee('Tahap 1: Form Permohonan Suket K3 Lingkungan Kerja');

        // 4. Create orders: one for regularUser, one for otherUser
        $orderUser = Permohonan::query()->create([
            'kode' => 'PMH-USER-001',
            'user_id' => $regularUser->id,
            'status_global' => 'selesai',
            'status_lab' => 'selesai',
        ]);
        DraftLhu::query()->create([
            'permohonan_id' => $orderUser->id,
            'signed_file_path' => 'draft_lhus/test_signed.pdf',
            'signed_file_name' => 'LHU_TTD_PMH-USER-001.pdf',
            'created_by' => $admin->id,
        ]);

        $orderOther = Permohonan::query()->create([
            'kode' => 'PMH-OTHER-002',
            'user_id' => $otherUser->id,
            'status_global' => 'selesai',
            'status_lab' => 'selesai',
        ]);

        // 5. User cannot submit order belonging to someone else
        $failOtherResponse = $this->actingAs($regularUser)->post('/permohonan-suket/store', [
            'nomor_order' => $orderOther->kode,
            'faktor_k3' => ['fisika'],
            'lhu_source' => 'auto',
        ]);
        $failOtherResponse->assertSessionHas('error');

        // 6. User submits their own order
        $submitResponse = $this->actingAs($regularUser)->post('/permohonan-suket/store', [
            'nomor_order' => $orderUser->kode,
            'faktor_k3' => ['fisika', 'kimia'],
            'lhu_source' => 'auto',
            'catatan' => 'Permohonan Suket K3 rutin unit pabrik',
        ]);
        $submitResponse->assertRedirect(route('user.suket.index'));

        $suket = SuketK3::where('nomor_order', $orderUser->kode)->latest('id')->first();
        $this->assertNotNull($suket);
        $this->assertEquals(2, $suket->status_tahap); // Auto-advanced to Tahap 2 for internal processing
        $this->assertEquals(['fisika', 'kimia'], $suket->faktor_k3);
        $this->assertEquals('auto', $suket->lhu_source);
        $this->assertEquals('draft_lhus/test_signed.pdf', $suket->lhu_file_path);

        // 7. Penguji K3 (pcu) completes Tahap 2 (Evaluasi Dokumen) -> advances to Tahap 3 (Penyusunan Suket)
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Hasil evaluasi NAB fisika dan kimia memenuhi standar Permenaker No. 5/2018',
        ])->assertRedirect(route('suket.index', ['stage' => 3]));

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);

        // 8. Test Document Preview without forcing download
        $previewResponse = $this->actingAs($pcu)->get("/suket-k3/{$suket->id}/preview/draft");
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('SURAT KETERANGAN');

        // 9. Tahap 3: Penguji K3 uploads revised draft
        $revisedFile = UploadedFile::fake()->create('revisi_draf_suket.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'upload_draft',
            'revised_draft' => $revisedFile,
            'catatan' => 'Draf diperbaiki klausul rekomendasi K3',
        ])->assertRedirect(route('suket.index', ['stage' => 3]));

        $suket->refresh();
        $this->assertNotNull($suket->draft_file_path);

        // 10. Gerbang QC Review: QC approves draf suket (Tahap 3 -> Tahap 4)
        $qcResponse = $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'approve',
            'catatan' => 'Draf dokumen Suket telah diverifikasi QC dan disetujui.',
        ]);
        $qcResponse->assertRedirect(route('suket.index', ['stage' => 4]));

        $suket->refresh();
        $this->assertEquals('approved', $suket->qc_status);
        $this->assertEquals(4, $suket->status_tahap);

        // 11. Tahap 4: Kepala Balai (mp) / Admin mengesahkan TTD dengan mengunggah berkas scan TTD/TTE
        $signedFile = UploadedFile::fake()->create('suket_sah_ttd.pdf', 150, 'application/pdf');
        $this->actingAs($mp)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'signed_document' => $signedFile,
            'catatan' => 'Surat Keterangan K3 telah ditandatangani Kepala Balai',
        ])->assertRedirect(route('suket.index', ['stage' => 5]));

        $suket->refresh();
        $this->assertEquals(5, $suket->status_tahap);
        $this->assertNotNull($suket->signed_file_path);
        $this->assertNotNull($suket->signed_at);

        // 12. Tahap 5: Admin meng-input nomor surat resmi (Auto-replace di draf dokumen)
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'nomor_surat' => '566/SK-LK/BK3-SBY/IX/2026',
            'catatan' => 'Surat keterangan K3 resmi diterbitkan',
        ])->assertRedirect(route('suket.index', ['stage' => 6]));

        $suket->refresh();
        $this->assertEquals(6, $suket->status_tahap);
        $this->assertEquals('566/SK-LK/BK3-SBY/IX/2026', $suket->nomor_surat);
        $this->assertNotNull($suket->published_at);

        // 13. Tahap 6: Admin menyerahkan langsung ke portal web pelanggan (tanpa resi fisik)
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Suket resmi telah tersedia di portal web Balai K3',
        ])->assertRedirect(route('suket.index', ['stage' => 6]));

        $suket->refresh();
        $this->assertNotNull($suket->sent_to_customer_at);
        $this->assertEquals('Portal Digital Web Balai K3', $suket->metode_pengiriman);

        // 14. Pemohon mengecek portal dan dapat melihat & preview suket yang sudah terbit
        $userCheckResponse = $this->actingAs($regularUser)->get('/permohonan-suket');
        $userCheckResponse->assertStatus(200);
        $userCheckResponse->assertSee('566/SK-LK/BK3-SBY/IX/2026');
        $userCheckResponse->assertSee('Lihat Suket');
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
            'action' => 'revision',
            'catatan' => 'Perbaiki klausul evaluasi ergonomi pada paragraf 2',
        ])->assertRedirect(route('suket.index', ['stage' => 3]));

        $suket->refresh();
        $this->assertEquals('revision', $suket->qc_status);
        $this->assertEquals('Perbaiki klausul evaluasi ergonomi pada paragraf 2', $suket->qc_note);
        $this->assertEquals(3, $suket->status_tahap); // Remains in Tahap 3

        // D. After revision, QC approves
        $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'approve',
            'catatan' => 'Sudah direvisi dan sesuai',
        ])->assertRedirect(route('suket.index', ['stage' => 4]));

        $suket->refresh();
        $this->assertEquals('approved', $suket->qc_status);
        $this->assertEquals(4, $suket->status_tahap);

        // E. Tahap 5 to 6 publishes suket
        $suket->update(['status_tahap' => 5, 'nomor_surat' => '566/SK-LK/BK3-SBY/09/2026']);
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Suket resmi diterbitkan',
        ])->assertSessionHasNoErrors();

        $suket->refresh();
        $this->assertEquals(6, $suket->status_tahap);
        $this->assertNotNull($suket->published_at);
    }

    public function test_evaluasi_dokumen_side_by_side_comments_and_rejection(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = User::create([
            'name' => 'Penguji K3 Evaluator',
            'email' => 'evaluator.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $pemohon = User::create([
            'name' => 'Pemohon PT Berkah',
            'email' => 'pemohon.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $suket = SuketK3::create([
            'user_id' => $pemohon->id,
            'nomor_order' => 'ORD-EVAL-001',
            'status_tahap' => 2,
            'faktor_k3' => ['fisika'],
            'perusahaan_nama' => 'PT Berkah Sentosa',
            'lokasi' => 'Surabaya',
            'lhu_file_path' => 'draft_lhus/test_eval_lhu.pdf',
        ]);

        // 1. Admin/Penguji menambahkan sorotan kesalahan dengan field bagian & highlight_text
        $cmt1Response = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/comment", [
            'bagian' => 'Halaman 3 - Titik Pengukuran Suhu',
            'highlight_text' => 'ISBB 34.5 C tanpa keterangan istirahat',
            'comment' => 'Nilai ISBB melebihi NAB, wajib mencantumkan pengaturan waktu kerja istirahat',
            'target' => 'pemohon',
        ]);
        $cmt1Response->assertSessionHas('success');

        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/comment", [
            'bagian' => 'Halaman 2 - Tabel Kebisingan',
            'highlight_text' => '89 dBA',
            'comment' => 'Mohon lengkapi rekomendasi APD hearing protection pada ruang genset',
            'target' => 'pemohon',
        ])->assertSessionHas('success');

        $this->assertCount(2, $suket->fresh()->comments);
        $this->assertDatabaseHas('suket_k3_comments', [
            'suket_id' => $suket->id,
            'bagian' => 'Halaman 2 - Tabel Kebisingan',
            'highlight_text' => '89 dBA',
        ]);

        // Uji coba hapus salah satu sorotan jika ada koreksi
        $firstCmt = $suket->fresh()->comments->first();
        $this->actingAs($admin)->delete("/suket-k3/{$suket->id}/comment/{$firstCmt->id}")
            ->assertSessionHas('success');
        $this->assertCount(1, $suket->fresh()->comments);

        // 2. Evaluasi ditolak oleh Penguji K3 (reject_evaluasi)
        $rejectRes = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'reject_evaluasi',
            'catatan' => 'Dokumen LHU belum lengkap data denah dan parameter NAB melebihi batas tanpa rekomendasi pengendalian',
        ]);
        $rejectRes->assertSessionHas('warning');

        $suket->refresh();
        $this->assertEquals(2, $suket->status_tahap);
        $this->assertEquals('rejected', $suket->evaluasi_status);
        $this->assertTrue($suket->isEvaluasiRejected());
        $this->assertEquals($admin->id, $suket->evaluasi_by);
        $this->assertNotNull($suket->evaluasi_at);

        // 3. User pemohon mengakses /permohonan-suket: melihat badge Evaluasi Perlu Revisi, bagian yang disorot, dan teks yang salah
        $userView = $this->actingAs($pemohon)->get('/permohonan-suket');
        $userView->assertStatus(200);
        $userView->assertSee('Evaluasi Perlu Revisi');
        $userView->assertSee('Hasil Evaluasi LHU Memerlukan Perbaikan / Revisi');
        $userView->assertSee('Halaman 2 - Tabel Kebisingan');
        $userView->assertSee('89 dBA');
        $userView->assertSee("modalUserEvaluasiLhu{$suket->id}");
        $userView->assertSee('Tunjukkan di Dokumen');
        $userView->assertSee('lhu-annotator.js');
        $userView->assertSee('data-bs-target="#modalUserEvaluasiLhu' . $suket->id . '"', false);

        // Uji tampilan admin side-by-side: memastikan tombol floating Google Docs dan PDF annotator ter-render
        $adminView = $this->actingAs($admin)->get('/suket-k3?stage=2');
        $adminView->assertStatus(200);
        $adminView->assertSee("modalEvaluasiSideBySide{$suket->id}");
        $adminView->assertSee("gdocsFloatingBtn{$suket->id}");
        $adminView->assertSee("evalSidePanelScroll{$suket->id}");
        $adminView->assertSee("cardAddComment{$suket->id}");
        $adminView->assertSee('lhu-annotator.js');
        $adminView->assertSee('Pengajuan suket didaftarkan melalui Nomor Order ' . $suket->nomor_order);

        // 4. Setelah diperbaiki, Penguji K3 menyetujui Evaluasi Dokumen
        $approveRes = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'LHU dan data pendukung telah diverifikasi dan memenuhi Permenaker No. 5/2018',
        ]);
        $approveRes->assertSessionHas('success');

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);
        $this->assertEquals('approved', $suket->evaluasi_status);

        // 5. Di Tahap 3, Penguji K3 menginput nomor_surat sebelum penandatanganan
        $suket->update(['qc_status' => 'approved']); // QC sudah approved
        $advanceT3 = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'nomor_surat' => '566/SK-LK/BK3-SBY/09/2026',
            'tanggal_surat' => '2026-09-16',
            'catatan' => 'Diajukan ke penandatanganan Kepala Balai',
        ]);
        $advanceT3->assertSessionHas('success');

        $suket->refresh();
        $this->assertEquals(4, $suket->status_tahap);
        $this->assertEquals('566/SK-LK/BK3-SBY/09/2026', $suket->nomor_surat);
        $this->assertEquals('2026-09-16', $suket->tanggal_surat?->format('Y-m-d'));

        // 6. User melihat progres Tahap 4 dengan Nomor Surat resmi tercantum
        $userViewT4 = $this->actingAs($pemohon)->get('/permohonan-suket');
        $userViewT4->assertStatus(200);
        $userViewT4->assertSee('Tahap 4: Penandatanganan Suket');
        $userViewT4->assertSee('566/SK-LK/BK3-SBY/09/2026');
    }

    public function test_tahap_4_upload_signed_doc_file_advances_to_stage_5(): void
    {
        \Illuminate\Support\Facades\Storage::fake('private');

        $admin = User::create([
            'name' => 'Admin Test TTD',
            'email' => 'admin.ttd.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $suket = SuketK3::create([
            'nomor_order' => 'PMH-TEST-TTD-01',
            'status_tahap' => 4,
            'faktor_k3' => ['fisika'],
            'qc_status' => 'approved',
            'perusahaan_nama' => 'PT Uji TTD',
            'lokasi' => 'Surabaya',
        ]);

        // Upload a .doc file whose content is HTML (exactly like Word HTML draft)
        $docContent = '<html><body><h1>Dokumen Suket Disahkan</h1></body></html>';
        $signedDoc = UploadedFile::fake()->createWithContent('Draft_Suket_Signed.doc', $docContent);

        $response = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'signed_document' => $signedDoc,
            'catatan' => 'Suket telah ditandatangani basah oleh Kepala Balai',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('suket.index', ['stage' => 5]));

        $suket->refresh();
        $this->assertEquals(5, $suket->status_tahap);
        $this->assertNotNull($suket->signed_file_path);
        $this->assertEquals('Draft_Suket_Signed.doc', $suket->signed_file_name);
        $this->assertNotNull($suket->signed_at);
        $this->assertEquals($admin->id, $suket->signed_by);
    }

    public function test_user_cannot_view_or_download_unreleased_stage_6_suket()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $user = User::create([
            'name' => 'User Pemohon Gate',
            'email' => 'user.gate@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $admin = User::create([
            'name' => 'Admin Gate',
            'email' => 'admin.gate@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $signedDoc = UploadedFile::fake()->create('Suket_Resmi_TTD.pdf', 200, 'application/pdf');
        $signedPath = $signedDoc->storeAs('suket_docs/test', 'Suket_Resmi_TTD.pdf', 'local');

        $suket = SuketK3::create([
            'user_id' => $user->id,
            'nomor_order' => 'ORD-STAGE6-TEST',
            'status_tahap' => 6, // Di tahap 6
            'nomor_surat' => '566/SK-LK/BK3-SBY/09/2026',
            'signed_file_path' => $signedPath,
            'signed_file_name' => 'Suket_Resmi_TTD.pdf',
            'sent_to_customer_at' => null, // BELUM DISERAHKAN
            'perusahaan_nama' => 'PT Uji Gate',
            'lokasi' => 'Surabaya',
        ]);

        // 1. User melihat halaman portal: berstatus Sedang Diproses dan TIDAK ada tombol Lihat Suket Resmi
        $pageResponse = $this->actingAs($user)->get('/permohonan-suket');
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Sedang Diproses');
        $pageResponse->assertDontSee('Lihat Suket Resmi');

        // 2. User mencoba preview langsung via URL: 403 Forbidden
        $previewBlocked = $this->actingAs($user)->get("/permohonan-suket/{$suket->id}/preview/signed");
        $previewBlocked->assertStatus(403);

        // 3. User mencoba download langsung via URL: 403 Forbidden
        $downloadBlocked = $this->actingAs($user)->get("/permohonan-suket/{$suket->id}/download/signed");
        $downloadBlocked->assertStatus(403);

        // 4. Admin melakukan Penyerahan Suket
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Suket diserahkan ke akun pemohon',
        ]);

        $suket->refresh();
        $this->assertNotNull($suket->sent_to_customer_at);

        // 5. Setelah tuntas diserahkan, user dapat melihat tombol dan mengakses dokumen
        $pageResponseAfter = $this->actingAs($user)->get('/permohonan-suket');
        $pageResponseAfter->assertStatus(200);
        $pageResponseAfter->assertSee('Tuntas Diserahkan');
        $pageResponseAfter->assertSee('Lihat Suket Resmi');

        // Test preview berkas PDF
        $previewAllowed = $this->actingAs($user)->get("/permohonan-suket/{$suket->id}/preview/signed");
        $previewAllowed->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $previewAllowed->headers->get('Content-Type'));

        // Test preview berkas Word (.doc/HTML template)
        \Illuminate\Support\Facades\Storage::disk('local')->put('suket_docs/test/Suket_Word.doc', 'Sample Word Content');
        $suket->signed_file_path = 'suket_docs/test/Suket_Word.doc';
        $suket->signed_at = now();
        $suket->save();
        $previewWord = $this->actingAs($user)->get("/permohonan-suket/{$suket->id}/preview/signed");
        $previewWord->assertStatus(200);
        $this->assertStringContainsString('text/html', (string) $previewWord->headers->get('Content-Type'));
        $previewWord->assertSee('SURAT KETERANGAN');
        $previewWord->assertSee('TERTANDATANGANI SECARA ELEKTRONIK (TTE)');
        $previewWord->assertSee('WordSection1');

        $downloadAllowed = $this->actingAs($user)->get("/permohonan-suket/{$suket->id}/download/signed");
        $downloadAllowed->assertStatus(200);
    }
}

