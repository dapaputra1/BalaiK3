<?php

namespace Tests\Feature;

use App\Models\BillingPaymentGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillingGuideFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_role_can_get_billing_guide_metadata(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('billing-guide.active'));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'exists' => false,
            ]);
    }

    public function test_internal_role_can_upload_billing_guide_pdf(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Storage::fake('local');

        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $file = UploadedFile::fake()->createWithContent('panduan-pembayaran.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        $response = $this->actingAs($superadmin)->post(route('billing-guide.upload'), [
            'guide_file' => $file,
        ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Panduan pembayaran berhasil diupload.',
            ]);

        $guide = BillingPaymentGuide::query()->latest('id')->first();

        $this->assertNotNull($guide);
        $this->assertSame('panduan-pembayaran.pdf', $guide->file_name);
        Storage::disk('local')->assertExists($guide->file_path);
    }

    public function test_non_internal_user_cannot_upload_billing_guide_pdf(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $file = UploadedFile::fake()->create('panduan-pembayaran.pdf', 200, 'application/pdf');

        $response = $this->actingAs($user)->post(route('billing-guide.upload'), [
            'guide_file' => $file,
        ]);

        $response->assertForbidden();
    }

    public function test_authenticated_user_can_download_billing_guide_when_available(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $storedPath = UploadedFile::fake()
            ->create('panduan-pembayaran.pdf', 200, 'application/pdf')
            ->storeAs('billing-guides', 'panduan-pembayaran.pdf', 'local');

        BillingPaymentGuide::query()->create([
            'file_name' => 'panduan-pembayaran.pdf',
            'file_path' => $storedPath,
            'uploaded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('billing-guide.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
