<?php

namespace Tests\Feature;

use App\Models\Permohonan;
use App\Models\SuketK3;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuketK3WorkflowTest extends TestCase
{
    public function test_suket_menu_and_pages_for_internal_roles()
    {
        $admin = User::where('role', 'admin')->first();
        $pcu = User::where('role', 'pcu')->first();
        $mp = User::where('role', 'mp')->first();
        $regularUser = User::where('role', 'user')->first();

        // 1. Regular user cannot access suket
        $this->actingAs($regularUser)
            ->get('/suket-k3')
            ->assertStatus(403);

        // 2. Admin can access and see the standalone Suket K3 menu
        $response = $this->actingAs($admin)->get('/suket-k3');
        $response->assertStatus(200);
        $response->assertSee('Penerbitan Surat Keterangan (Suket) K3 Lingkungan Kerja');
        $response->assertSee('Input Pengajuan Suket Baru');
        $response->assertSee('Alur Status Suket (6 Tahap)');

        // Check sidebar has the standalone menu item
        $response->assertSee('Suket K3 Lingkungan Kerja');

        // 3. Admin submits by Nomor Order
        $permohonan = Permohonan::first();
        $submitResponse = $this->actingAs($admin)->post('/suket-k3/store-by-order', [
            'nomor_order' => $permohonan->kode,
            'catatan' => 'Uji test otomatis submit order',
        ]);
        $submitResponse->assertRedirect(route('suket.index'));

        $suket = SuketK3::where('nomor_order', $permohonan->kode)->latest('id')->first();
        $this->assertNotNull($suket);
        $this->assertEquals(1, $suket->status_tahap);

        // 4. Penguji K3 (pcu) can advance from Tahap 1 to Tahap 2 (Evaluasi Dokumen)
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Pemeriksaan kelengkapan dokumen pengujian K3',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(2, $suket->status_tahap);

        // 5. Penguji K3 can advance from Tahap 2 to Tahap 3 (Penyusunan Suket)
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Dokumen evaluasi valid dan disetujui',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(3, $suket->status_tahap);

        // 6. Penguji K3 advances to Tahap 4 (Diajukan ke Kepala Balai)
        $this->actingAs($pcu)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Draft suket selesai disusun, diajukan untuk TTD',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(4, $suket->status_tahap);

        // 7. Kepala Balai (mp) approves/signs at Tahap 4
        $this->actingAs($mp)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'catatan' => 'Tanda tangan elektronik Kepala Balai disetujui',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(5, $suket->status_tahap);
        $this->assertNotNull($suket->signed_at);

        // 8. Admin publishes & sends to customer (Tahap 5 -> Tahap 6)
        $this->actingAs($admin)->post("/suket-k3/{$suket->id}/advance", [
            'action' => 'next',
            'resi_pengiriman' => 'JNE-K3-99887766',
            'metode_pengiriman' => 'Kurir Ekspedisi',
            'catatan' => 'Dokumen fisik dikirimkan ke alamat perusahaan pemohon',
        ])->assertRedirect(route('suket.index'));

        $suket->refresh();
        $this->assertEquals(6, $suket->status_tahap);
        $this->assertNotNull($suket->sent_to_customer_at);
        $this->assertEquals('JNE-K3-99887766', $suket->resi_pengiriman);
    }
}
