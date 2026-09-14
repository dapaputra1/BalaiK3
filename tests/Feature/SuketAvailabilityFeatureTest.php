<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuketAvailabilityFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_toggle_suket_availability(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)->put(route('superadmin.suket.settings.availability'), [
            'enabled' => false,
        ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'enabled' => false,
            ]);

        $this->assertSame('0', AppSetting::query()->where('key', 'suket_penerbitan_enabled')->value('value'));
    }

    public function test_invoice_submit_skips_suket_stage_when_disabled(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        AppSetting::query()->updateOrCreate(
            ['key' => 'suket_penerbitan_enabled'],
            ['value' => '0']
        );

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);
        $customer = User::factory()->create([
            'role' => 'user',
        ]);
        $permohonan = Permohonan::query()->create([
            'kode' => 'INV-SUKET-001',
            'user_id' => $customer->id,
            'status_global' => 'invoice',
            'status_lab' => 'invoice',
        ]);
        DraftLhu::query()->create([
            'permohonan_id' => $permohonan->id,
            'billing_verified_at' => now(),
            'created_by' => $superadmin->id,
        ]);

        $response = $this->actingAs($superadmin)->post(route('superadmin.invoice.submit', $permohonan));

        $response->assertOk();

        $permohonan->refresh();
        $this->assertSame('penyerahan_lhu', $permohonan->status_global);
        $this->assertSame('penyerahan_lhu', $permohonan->status_lab);

        $suketStep = WorkflowStep::query()->where('kode', 'penerbitan_suket')->first();
        $penyerahanStep = WorkflowStep::query()->where('kode', 'penyerahan_lhu')->first();

        $this->assertNotNull($suketStep);
        $this->assertNotNull($penyerahanStep);

        $this->assertDatabaseHas('permohonan_steps', [
            'permohonan_id' => $permohonan->id,
            'step_id' => $suketStep->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('permohonan_steps', [
            'permohonan_id' => $permohonan->id,
            'step_id' => $penyerahanStep->id,
            'status' => 'pending',
        ]);
    }

    public function test_penyerahan_lhu_can_be_sent_without_suket_when_disabled(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Storage::fake('local');

        AppSetting::query()->updateOrCreate(
            ['key' => 'suket_penerbitan_enabled'],
            ['value' => '0']
        );

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);
        $customer = User::factory()->create([
            'role' => 'user',
        ]);
        $permohonan = Permohonan::query()->create([
            'kode' => 'SERAH-SUKET-001',
            'user_id' => $customer->id,
            'status_global' => 'penyerahan_lhu',
            'status_lab' => 'penyerahan_lhu',
        ]);

        Storage::disk('local')->put('ttd-lhu/' . $permohonan->id . '/signed.pdf', 'signed-content');

        DraftLhu::query()->create([
            'permohonan_id' => $permohonan->id,
            'signed_file_path' => 'ttd-lhu/' . $permohonan->id . '/signed.pdf',
            'signed_file_name' => 'signed.pdf',
            'billing_verified_at' => now(),
            'created_by' => $superadmin->id,
        ]);

        $penyerahanStep = WorkflowStep::query()->create([
            'kode' => 'penyerahan_lhu',
            'nama' => 'Penyerahan LHU',
            'urutan' => 20,
        ]);

        PermohonanStep::query()->create([
            'permohonan_id' => $permohonan->id,
            'step_id' => $penyerahanStep->id,
            'status' => 'pending',
            'started_at' => now(),
            'updated_by' => $superadmin->id,
        ]);

        $response = $this->actingAs($superadmin)->post(route('superadmin.penyerahan-lhu.send', $permohonan));

        $response->assertOk();

        $draft = $permohonan->draftLhu()->first();
        $this->assertNotNull($draft?->lhu_sent_to_user_at);
    }
}
