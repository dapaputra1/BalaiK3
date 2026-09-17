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

        // 10. Tahap 3: Penguji K3 mengirim draf ke Tim QC (bukan langsung ke penandatanganan)
        $sendQcRes = $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'send_to_qc',
            'catatan' => 'Draf dokumen diajukan ke Tim QC untuk peninjauan.',
        ]);
        $sendQcRes->assertRedirect(route('suket.index', ['stage' => 3]));
        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);
        $this->assertEquals('pending', $suket->qc_status);

        // 11. Gerbang QC Review: QC mengisikan nomor surat & menyetujui draf (Tahap 3 -> Tahap 4)
        $qcResponse = $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'approve',
            'nomor_surat' => '566/SK-LK/BK3-SBY/IX/2026',
            'tanggal_surat' => '2026-09-16',
            'catatan' => 'Draf dokumen Suket telah diverifikasi QC dan disetujui.',
        ]);
        $qcResponse->assertRedirect(route('suket.index', ['stage' => 4]));

        $suket->refresh();
        $this->assertEquals('approved', $suket->qc_status);
        $this->assertEquals(4, $suket->status_tahap);
        $this->assertEquals('566/SK-LK/BK3-SBY/IX/2026', $suket->nomor_surat);

        // Pastikan histori tercatat di suket_k3_histories
        $this->assertDatabaseHas('suket_k3_histories', [
            'suket_id' => $suket->id,
            'action' => 'send_to_qc',
        ]);
        $this->assertDatabaseHas('suket_k3_histories', [
            'suket_id' => $suket->id,
            'action' => 'qc_approved',
            'nomor_surat' => '566/SK-LK/BK3-SBY/IX/2026',
        ]);

        // 12. Tahap 4: Kepala Balai (mp) / Admin mengesahkan TTD dengan mengunggah berkas scan TTD/TTE
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

        // B. Di Tahap 3, aksi pengiriman mengarahkan ke QC
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'send_to_qc',
            'catatan' => 'Kirim draf ke QC',
        ])->assertRedirect(route('suket.index', ['stage' => 3]));
        $this->assertEquals(3, $suket->fresh()->status_tahap);
        $this->assertEquals('pending', $suket->fresh()->qc_status);

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

        // 5. Di Tahap 3, Penguji K3 mengajukan draf ke QC, lalu QC menetapkan nomor surat saat approve
        $advanceT3 = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'send_to_qc',
            'catatan' => 'Draf diajukan ke QC',
        ]);
        $advanceT3->assertSessionHas('success');
        $this->assertEquals(3, $suket->fresh()->status_tahap);

        // QC mereview, menetapkan nomor surat, dan menyetujui ke Tahap 4
        $qcApproveRes = $this->actingAs($admin)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'approve',
            'nomor_surat' => '566/SK-LK/BK3-SBY/09/2026',
            'tanggal_surat' => '2026-09-16',
            'catatan' => 'Disetujui QC dan nomor surat resmi ditetapkan.',
        ]);
        $qcApproveRes->assertSessionHas('success');

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

    public function test_qc_return_to_penyusunan_and_history_logging_in_ui(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = User::create([
            'name' => 'Admin History Test',
            'email' => 'admin.hist@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $qc = User::create([
            'name' => 'QC Reviewer Test',
            'email' => 'qc.hist@example.com',
            'password' => bcrypt('password'),
            'role' => 'qc',
        ]);

        $suket = SuketK3::create([
            'nomor_order' => 'ORD-HIST-001',
            'status_tahap' => 3,
            'qc_status' => 'pending',
            'faktor_k3' => ['fisika'],
            'perusahaan_nama' => 'PT Riwayat Berkah',
            'lokasi' => 'Surabaya',
        ]);

        // 1. Penguji mengirim draf ke QC
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'send_to_qc',
            'catatan' => 'Mohon ditinjau draf suket kami.',
        ])->assertRedirect(route('suket.index', ['stage' => 3]));

        // 2. QC mengembalikan ke penyusunan dengan revisi
        $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'revision',
            'catatan' => 'Perbaiki penulisan parameter kebisingan pada lampiran 1.',
        ])->assertRedirect(route('suket.index', ['stage' => 3]));

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);
        $this->assertEquals('revision', $suket->qc_status);
        $this->assertEquals('Perbaiki penulisan parameter kebisingan pada lampiran 1.', $suket->qc_note);

        // 3. Verifikasi tabel log history
        $this->assertDatabaseHas('suket_k3_histories', [
            'suket_id' => $suket->id,
            'action' => 'send_to_qc',
            'catatan' => 'Mohon ditinjau draf suket kami.',
        ]);
        $this->assertDatabaseHas('suket_k3_histories', [
            'suket_id' => $suket->id,
            'action' => 'qc_returned',
            'catatan' => 'Perbaiki penulisan parameter kebisingan pada lampiran 1.',
        ]);

        // 4. Admin melihat halaman index: tombol Log Riwayat dan modal history tampil
        $response = $this->actingAs($admin)->get('/suket-k3?stage=3');
        $response->assertStatus(200);
        $response->assertSee('Log Riwayat');
        $response->assertSee("modalHistory{$suket->id}");
        $response->assertSee('Perbaiki penulisan parameter kebisingan pada lampiran 1.');
        $response->assertSee('QC Mengembalikan Berkas ke Penyusunan');
    }

    public function test_strict_separation_between_penyusunan_and_review_qc_tabs_and_buttons(): void
    {
        $admin = User::create([
            'name' => 'Admin Tab Test',
            'email' => 'admin.tab.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $qc = User::create([
            'name' => 'QC Tab Test',
            'email' => 'qc.tab.' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'qc',
        ]);

        $suket = SuketK3::create([
            'nomor_order' => 'ORD-SEPARATION-01',
            'status_tahap' => 3,
            'qc_status' => null, // Masih proses penyusunan
            'draft_file_path' => 'suket_docs/test/draf.docx',
            'perusahaan_nama' => 'PT Pemisahan Tab',
            'lokasi' => 'Surabaya',
        ]);

        // 1. Di Penyusunan Suket (stage=3):
        // - Suket harus ada di tab stage=3
        // - Suket TIDAK boleh ada di tab stage=qc
        $stage3View = $this->actingAs($admin)->get('/suket-k3?stage=3');
        $stage3View->assertStatus(200);
        $stage3View->assertSee('ORD-SEPARATION-01');
        $stage3View->assertSee("modalAdvance{$suket->id}");
        $stage3View->assertSee('Kirim ke QC');
        $stage3View->assertDontSee("modalQc{$suket->id}"); // BUG 2 FIXED: Tidak boleh ada tombol Review QC di stage 3

        $stageQcView = $this->actingAs($qc)->get('/suket-k3?stage=qc');
        $stageQcView->assertStatus(200);
        $stageQcView->assertDontSee('ORD-SEPARATION-01'); // Belum dikirim ke QC

        // 2. Kirim ke QC:
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'send_to_qc',
            'catatan' => 'Draf dikirim ke Tim QC',
        ])->assertSessionHas('success');

        $suket->refresh();
        $this->assertEquals('pending', $suket->qc_status);

        // 3. Setelah dikirim ke QC:
        // - Suket HARUS HILANG dari tab stage=3
        // - Suket HARUS MUNCUL di tab stage=qc
        $stage3ViewAfter = $this->actingAs($admin)->get('/suket-k3?stage=3');
        $stage3ViewAfter->assertStatus(200);
        $stage3ViewAfter->assertDontSee('<div class="fw-bold text-dark fs-6">ORD-SEPARATION-01</div>', false); // BUG 1 FIXED: Hilang dari tabel stage 3
        $stage3ViewAfter->assertDontSee("modalAdvance{$suket->id}");
        $stage3ViewAfter->assertDontSee("modalQc{$suket->id}");
        $stage3ViewAfter->assertDontSee("modalUploadDraft{$suket->id}");

        $stageQcViewAfter = $this->actingAs($qc)->get('/suket-k3?stage=qc');
        $stageQcViewAfter->assertStatus(200);
        $stageQcViewAfter->assertSee('<div class="fw-bold text-dark fs-6">ORD-SEPARATION-01</div>', false); // MUNCUL di tabel stage QC
        $stageQcViewAfter->assertSee("modalQc{$suket->id}"); // BUG 2 FIXED: Ada tombol Review QC di stage QC
        $stageQcViewAfter->assertDontSee("modalAdvance{$suket->id}"); // BUG 3 FIXED: Tidak ada tombol Kirim ke QC di stage QC
        $stageQcViewAfter->assertDontSee("modalUploadDraft{$suket->id}");

        // 4. QC Mengembalikan ke Penyusunan:
        $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'revision',
            'catatan' => 'Mohon perbaiki klausul baku mutu lingkungan kerja.',
        ])->assertSessionHas('warning');

        $suket->refresh();
        $this->assertEquals('revision', $suket->qc_status);

        // 5. Setelah dikembalikan ke penyusunan:
        // - Suket KEMBALI MUNCUL di tab stage=3 dengan tombol "Kirim ke QC", tanpa "Review QC"
        // - Suket HILANG dari tab stage=qc
        $stage3ViewRevisi = $this->actingAs($admin)->get('/suket-k3?stage=3');
        $stage3ViewRevisi->assertStatus(200);
        $stage3ViewRevisi->assertSee('<div class="fw-bold text-dark fs-6">ORD-SEPARATION-01</div>', false);
        $stage3ViewRevisi->assertSee("modalAdvance{$suket->id}");
        $stage3ViewRevisi->assertSee('Kirim ke QC');
        $stage3ViewRevisi->assertDontSee("modalQc{$suket->id}");

        $stageQcViewRevisi = $this->actingAs($qc)->get('/suket-k3?stage=qc');
        $stageQcViewRevisi->assertStatus(200);
        $stageQcViewRevisi->assertDontSee('<div class="fw-bold text-dark fs-6">ORD-SEPARATION-01</div>', false); // Hilang dari tabel QC
        $stageQcViewRevisi->assertDontSee("modalQc{$suket->id}");
    }

    public function test_evaluasi_revisi_ke_pemohon_and_pemohon_submit_revision()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $user = User::create([
            'name' => 'Pemohon PT Berjaya',
            'email' => 'berjaya@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
        $penguji = User::create([
            'name' => 'Penguji K3 Lapangan',
            'email' => 'penguji.lap@example.com',
            'password' => bcrypt('password'),
            'role' => 'pcu',
        ]);

        $order = Permohonan::query()->create([
            'kode' => 'ORD-REV-001',
            'user_id' => $user->id,
            'status_global' => 'selesai',
            'status_lab' => 'selesai',
        ]);

        $suket = SuketK3::create([
            'user_id' => $user->id,
            'permohonan_id' => $order->id,
            'nomor_order' => $order->kode,
            'status_tahap' => 2,
            'evaluasi_status' => 'pending',
            'faktor_k3' => ['fisika'],
            'lhu_source' => 'manual',
            'lhu_file_path' => 'suket_docs/lhu/sample.pdf',
            'lhu_file_name' => 'sample.pdf',
            'perusahaan_nama' => 'PT Berjaya Sukses',
            'lokasi' => 'Surabaya',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // 1. Penguji K3 menambahkan sorotan catatan (baik teks biasa maupun format BOX area scan)
        $commentRes1 = $this->actingAs($penguji)->post("/suket-k3/{$suket->id}/comment", [
            'bagian' => 'Halaman 1',
            'highlight_text' => '85 dBA',
            'comment' => 'Nilai kebisingan di ruang genset melebihi NAB tapi belum disertai rekomendasi APD.',
        ]);
        $commentRes1->assertSessionHas('success');

        $commentRes2 = $this->actingAs($penguji)->post("/suket-k3/{$suket->id}/comment", [
            'bagian' => 'Halaman 2 (Area Scan/Box)',
            'highlight_text' => '[BOX:2,15.50,30.00,50.00,20.00]',
            'comment' => 'Tabel hasil scan tidak terbaca jelas pada bagian tanda tangan teknisi.',
        ]);
        $commentRes2->assertSessionHas('success');

        $this->assertEquals(2, $suket->comments()->count());

        // 2. Penguji K3 meminta revisi ke pemohon (reject_evaluasi)
        $rejectRes = $this->actingAs($penguji)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'reject_evaluasi',
            'catatan' => 'Mohon lakukan revisi dokumen LHU dan beri keterangan klarifikasi pada APD genset.',
        ]);
        $rejectRes->assertSessionHas('warning');

        $suket->refresh();
        $this->assertEquals('rejected', $suket->evaluasi_status);
        $this->assertTrue($suket->isEvaluasiRejected());
        $this->assertDatabaseHas('suket_k3_histories', [
            'suket_id' => $suket->id,
            'action' => 'evaluasi_rejected',
        ]);

        // 3. Pemohon membuka portal permohonan dan melihat notifikasi revisi & modal revisi
        $portalRes = $this->actingAs($user)->get('/permohonan-suket');
        $portalRes->assertStatus(200);
        $portalRes->assertSee('Hasil Evaluasi LHU Memerlukan Perbaikan / Revisi');
        $portalRes->assertSee("modalRevisiPemohon{$suket->id}");
        $portalRes->assertSee('Kirim Revisi');

        // 4. Pemohon mengirimkan tanggapan dan revisi dokumen baru
        $fakePdf = UploadedFile::fake()->create('LHU_Revisi_Resmi.pdf', 200, 'application/pdf');
        $revisionRes = $this->actingAs($user)->post("/permohonan-suket/{$suket->id}/submit-revision", [
            'catatan_revisi' => 'Kami telah melampirkan LHU baru dengan spesifikasi APD earmuff dan scan tanda tangan yang jelas.',
            'lhu_file' => $fakePdf,
        ]);
        $revisionRes->assertSessionHas('success');
        $revisionRes->assertRedirect(route('user.suket.index'));

        // 5. Status evaluasi harus kembali ke 'pending' agar ditelaah ulang oleh Penguji K3
        $suket->refresh();
        $this->assertEquals('pending', $suket->evaluasi_status);
        $this->assertEquals('manual', $suket->lhu_source);
        $this->assertEquals('LHU_Revisi_Resmi.pdf', $suket->lhu_file_name);
        $this->assertNotNull($suket->lhu_file_path);
        $this->assertEquals('Kami telah melampirkan LHU baru dengan spesifikasi APD earmuff dan scan tanda tangan yang jelas.', $suket->catatan_revisi_pemohon);
        $this->assertNotNull($suket->revisi_pemohon_at);

        // Pastikan penjelasan pemohon TIDAK masuk ke suket_k3_comments (agar tidak tercampur dengan sorotan kesalahan)
        $this->assertEquals(2, $suket->comments()->count());
        $this->assertDatabaseMissing('suket_k3_comments', [
            'suket_id' => $suket->id,
            'comment' => '[Tanggapan & Revisi Pemohon]: Kami telah melampirkan LHU baru dengan spesifikasi APD earmuff dan scan tanda tangan yang jelas.',
        ]);

        $this->assertDatabaseHas('suket_k3_histories', [
            'suket_id' => $suket->id,
            'action' => 'user_revised',
        ]);

        // Penguji membuka modal evaluasi di /suket-k3?stage=2 dan melihat kotak terpisah penjelasan revisi pemohon
        $evaluatorViewRes = $this->actingAs($penguji)->get('/suket-k3?stage=2');
        $evaluatorViewRes->assertStatus(200);
        $evaluatorViewRes->assertSee('Penjelasan / Tanggapan Revisi Pemohon:');
        $evaluatorViewRes->assertSee('Kami telah melampirkan LHU baru dengan spesifikasi APD earmuff dan scan tanda tangan yang jelas.');
        $evaluatorViewRes->assertSee('Instruksi Koreksi Evaluator:');

        // 6. Penguji K3 meninjau kembali dan menyetujui evaluasi -> Melangkah ke Tahap 3
        $approveRes = $this->actingAs($penguji)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Revisi pemohon telah sesuai dan memenuhi standar Permenaker 05/2018.',
        ]);
        $approveRes->assertSessionHas('success');

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);
        $this->assertEquals('approved', $suket->evaluasi_status);
    }

    public function test_qc_annotates_draft_suket_and_returns_to_penyusunan_stage()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $qc = User::create([
            'name' => 'QC Reviewer',
            'email' => 'qc.evaluator@example.com',
            'password' => bcrypt('password'),
            'role' => 'qc',
        ]);
        $penguji = User::create([
            'name' => 'Penguji K3',
            'email' => 'penguji.stage3@example.com',
            'password' => bcrypt('password'),
            'role' => 'pcu',
        ]);
        $companyUser = User::create([
            'name' => 'PT Manufaktur Suket',
            'email' => 'pt.manufaktur@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $order = Permohonan::create([
            'kode' => 'ORD-QC-ANNOTATE-001',
            'user_id' => $companyUser->id,
            'status_global' => 'selesai',
        ]);

        $suket = SuketK3::create([
            'user_id' => $companyUser->id,
            'permohonan_id' => $order->id,
            'nomor_order' => $order->kode,
            'status_tahap' => 3,
            'evaluasi_status' => 'approved',
            'qc_status' => 'pending',
            'perusahaan_nama' => 'PT Manufaktur Suket',
            'lokasi' => 'Surabaya',
            'catatan' => 'Draf suket mohon ditinjau kelayakannya oleh tim QC.',
        ]);

        // 1. Tambah komentar evaluasi LHU terdahulu (Tahap 2)
        $suket->comments()->create([
            'user_id' => $penguji->id,
            'target' => 'pemohon',
            'document_type' => 'lhu',
            'bagian' => 'Halaman 1 - Hasil Uji Kebisingan',
            'highlight_text' => '85.4 dBA',
            'comment' => 'Koreksi penulisan satuan desibel',
        ]);

        // 2. QC melakukan preview PDF dari draf suket
        $previewPdfRes = $this->actingAs($qc)->get("/suket-k3/{$suket->id}/preview/draft_pdf");
        $previewPdfRes->assertStatus(200);
        $this->assertEquals('application/pdf', $previewPdfRes->headers->get('Content-Type'));

        // 3. QC menambahkan sorotan kesalahan pada draf suket (document_type = 'suket')
        $commentQc1 = $this->actingAs($qc)->post("/suket-k3/{$suket->id}/comment", [
            'document_type' => 'suket',
            'target' => 'internal',
            'bagian' => 'Halaman 1 - Klausul Menimbang',
            'highlight_text' => 'bahwa berdasarkan hasil pengujian lingkungan kerja',
            'comment' => 'Perbaiki penulisan nomor dasar hukum Permenaker 05/2018 pasal 5',
        ]);
        $commentQc1->assertSessionHas('success');

        $commentQc2 = $this->actingAs($qc)->post("/suket-k3/{$suket->id}/comment", [
            'document_type' => 'suket',
            'target' => 'internal',
            'bagian' => 'Halaman 1 (Area Scan/Box)',
            'highlight_text' => '[BOX:1,12.5%,34.2%,50.0%,20.0%]',
            'comment' => 'Format tabel faktor bahaya kurang rapi dan belum mencantumkan NAB',
        ]);
        $commentQc2->assertSessionHas('success');

        // Verifikasi pemisahan relasi lhuComments vs suketComments
        $suket->refresh();
        $this->assertEquals(1, $suket->lhuComments()->count());
        $this->assertEquals(2, $suket->suketComments()->count());
        $this->assertEquals(4, $suket->comments()->count() + 1); // 1 lhu + 2 suket = 3 comments

        // 4. QC mengambil keputusan: Kembalikan ke Penyusunan (Perlu Revisi)
        $returnRes = $this->actingAs($qc)->post("/suket-k3/{$suket->id}/qc-review", [
            'action' => 'revision',
            'catatan' => 'Mohon sesuaikan klausul menimbang dan perbaiki format tabel faktor bahaya sesuai poin sorotan QC.',
        ]);
        $returnRes->assertRedirect(route('suket.index', ['stage' => 3]));
        $returnRes->assertSessionHas('warning');

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);
        $this->assertEquals('revision', $suket->qc_status);
        $this->assertEquals('Mohon sesuaikan klausul menimbang dan perbaiki format tabel faktor bahaya sesuai poin sorotan QC.', $suket->qc_note);
        $this->assertEquals($qc->id, $suket->qc_by);

        // 5. Penguji K3 membuka /suket-k3?stage=3 dan melihat poin sorotan QC
        $stage3ViewRes = $this->actingAs($penguji)->get('/suket-k3?stage=3');
        $stage3ViewRes->assertStatus(200);
        $stage3ViewRes->assertSee('Catatan Arahan Revisi QC Sebelumnya:');
        $stage3ViewRes->assertSee('Mohon sesuaikan klausul menimbang dan perbaiki format tabel faktor bahaya sesuai poin sorotan QC.');
        $stage3ViewRes->assertSee('Daftar Arahan');
        $stage3ViewRes->assertSee('Sorotan QC');
        $stage3ViewRes->assertSee('Perbaiki penulisan nomor dasar hukum Permenaker 05/2018 pasal 5');
        $stage3ViewRes->assertSee('Format tabel faktor bahaya kurang rapi dan belum mencantumkan NAB');
    }
}

