<?php

namespace Tests\Feature;

use App\Models\Koding;
use App\Models\KodingItem;
use App\Models\Pengujian;
use App\Models\PengujianDokumen;
use App\Models\PengujianDokumenParameter;
use App\Models\PengujianLokasi;
use App\Models\Permohonan;
use App\Models\Prepanalisa;
use App\Models\PrepanalisaItem;
use App\Models\ServiceCategory;
use App\Models\ServiceParameter;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrepanalisaGroupingFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestSchema();
    }

    public function test_analis_can_claim_and_finish_same_parameter_as_one_group(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $analis = $this->createAnalis();
        [$permohonan, $items] = $this->createPrepanalisaItems($analis, 2);

        $groupPayload = $items->map(fn (PrepanalisaItem $item) => [
            'koding_item_id' => $item->koding_item_id,
            'pengujian_dokumen_parameter_id' => $item->pengujian_dokumen_parameter_id,
            'service_parameter_id' => $item->service_parameter_id,
            'kode_koding' => $item->kode_koding,
        ])->values()->all();

        $this->actingAs($analis)
            ->post(route('analis.prepanalisa.assign', $permohonan), [
                'items' => json_encode($groupPayload),
            ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Seluruh lokasi untuk parameter ini berhasil dipilih.',
            ]);

        $this->assertSame(
            2,
            PrepanalisaItem::query()
                ->whereIn('id', $items->pluck('id'))
                ->where('assigned_user_id', $analis->id)
                ->count()
        );

        $donePayload = collect($groupPayload)->map(fn (array $item) => [
            'koding_item_id' => $item['koding_item_id'],
            'pengujian_dokumen_parameter_id' => $item['pengujian_dokumen_parameter_id'],
        ])->all();

        $this->actingAs($analis)
            ->post(route('analis.prepanalisa.item-done', $permohonan), [
                'items' => json_encode($donePayload),
                'is_done' => true,
            ])
            ->assertOk();

        $this->assertSame(
            2,
            PrepanalisaItem::query()
                ->whereIn('id', $items->pluck('id'))
                ->where('is_done', true)
                ->count()
        );

        $this->actingAs($analis)
            ->post(route('analis.prepanalisa.reset-action', $permohonan), [
                'items' => json_encode($donePayload),
            ])
            ->assertOk();

        $this->assertSame(
            0,
            PrepanalisaItem::query()
                ->whereIn('id', $items->pluck('id'))
                ->whereNotNull('assigned_user_id')
                ->count()
        );
    }

    public function test_single_item_assignment_remains_supported(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $analis = $this->createAnalis();
        [$permohonan, $items] = $this->createPrepanalisaItems($analis, 1);
        $item = $items->first();

        $this->actingAs($analis)
            ->post(route('analis.prepanalisa.assign', $permohonan), [
                'koding_item_id' => $item->koding_item_id,
                'pengujian_dokumen_parameter_id' => $item->pengujian_dokumen_parameter_id,
                'service_parameter_id' => $item->service_parameter_id,
                'kode_koding' => $item->kode_koding,
            ])
            ->assertOk();

        $this->assertDatabaseHas('prepanalisa_items', [
            'id' => $item->id,
            'assigned_user_id' => $analis->id,
        ]);
    }

    public function test_group_assignment_rejects_mixed_parameters_without_partial_claim(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $analis = $this->createAnalis();
        [$permohonan, $items] = $this->createPrepanalisaItems($analis, 2, true);

        $payload = $items->map(fn (PrepanalisaItem $item) => [
            'koding_item_id' => $item->koding_item_id,
            'pengujian_dokumen_parameter_id' => $item->pengujian_dokumen_parameter_id,
            'service_parameter_id' => $item->service_parameter_id,
            'kode_koding' => $item->kode_koding,
        ])->values()->all();

        $this->actingAs($analis)
            ->post(route('analis.prepanalisa.assign', $permohonan), [
                'items' => json_encode($payload),
            ])
            ->assertStatus(422);

        $this->assertSame(
            0,
            PrepanalisaItem::query()
                ->whereIn('id', $items->pluck('id'))
                ->whereNotNull('assigned_user_id')
                ->count()
        );
    }

    private function createPrepanalisaItems(User $owner, int $count, bool $mixedParameters = false): array
    {
        $category = ServiceCategory::query()->create([
            'name' => 'Lingkungan Kerja',
            'short_code' => 'LK',
            'is_active' => true,
        ]);
        $parameters = collect([
            ServiceParameter::query()->create([
                'service_category_id' => $category->id,
                'name' => 'Nitrogen Dioksida',
                'short_code' => 'NO2',
                'price' => 100000,
                'is_active' => true,
            ]),
        ]);
        if ($mixedParameters) {
            $parameters->push(ServiceParameter::query()->create([
                'service_category_id' => $category->id,
                'name' => 'Sulfur Dioksida',
                'short_code' => 'SO2',
                'price' => 100000,
                'is_active' => true,
            ]));
        }

        $permohonan = Permohonan::query()->create([
            'kode' => 'REQ-' . fake()->unique()->numerify('#####'),
            'user_id' => $owner->id,
            'status_global' => 'preparasi_analisa',
        ]);
        $pengujian = Pengujian::query()->create([
            'permohonan_id' => $permohonan->id,
            'status' => 'submitted',
            'created_by' => $owner->id,
        ]);
        $koding = Koding::query()->create([
            'permohonan_id' => $permohonan->id,
            'status' => 'submitted',
            'created_by' => $owner->id,
        ]);
        $prepanalisa = Prepanalisa::query()->create([
            'permohonan_id' => $permohonan->id,
            'status' => 'draft',
            'created_by' => $owner->id,
        ]);

        $items = collect();
        for ($index = 0; $index < $count; $index++) {
            $parameter = $mixedParameters ? $parameters[$index] : $parameters->first();
            $lokasi = PengujianLokasi::query()->create([
                'pengujian_id' => $pengujian->id,
                'nama_lokasi' => 'Lokasi ' . ($index + 1),
                'urutan' => $index + 1,
            ]);
            $dokumen = PengujianDokumen::query()->create([
                'lokasi_id' => $lokasi->id,
                'label' => 'Dokumen ' . ($index + 1),
                'urutan' => 1,
            ]);
            $docParam = PengujianDokumenParameter::query()->create([
                'dokumen_id' => $dokumen->id,
                'service_parameter_id' => $parameter->id,
                'qty' => 1,
                'is_direct' => false,
                'is_sesuai' => true,
                'urutan' => 1,
            ]);
            $kode = 'J.22.083/E.' . ($index + 1) . '/1';
            $kodingItem = KodingItem::query()->create([
                'koding_id' => $koding->id,
                'pengujian_dokumen_id' => $dokumen->id,
                'pengujian_dokumen_parameter_id' => $docParam->id,
                'kode' => $kode,
            ]);
            $items->push(PrepanalisaItem::query()->create([
                'prepanalisa_id' => $prepanalisa->id,
                'koding_item_id' => $kodingItem->id,
                'pengujian_dokumen_parameter_id' => $docParam->id,
                'service_parameter_id' => $parameter->id,
                'kode_koding' => $kode,
                'created_by' => $owner->id,
            ]));
        }

        return [$permohonan, $items];
    }

    private function createAnalis(): User
    {
        return User::query()->create([
            'name' => 'Analis Uji',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'analis',
            'is_active' => true,
        ]);
    }

    private function createTestSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->boolean('is_active')->default(true);
            $table->string('signature_path')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_code')->unique();
            $table->string('short_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('service_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_category_id');
            $table->string('name');
            $table->string('system_code')->unique();
            $table->string('short_code')->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('permohonans', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->unsignedInteger('user_id');
            $table->string('status_global')->default('submitted');
            $table->timestamps();
        });
        Schema::create('pengujian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permohonan_id');
            $table->string('status')->default('draft');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('pengujian_lokasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengujian_id');
            $table->string('nama_lokasi');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
        Schema::create('pengujian_dokumen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lokasi_id');
            $table->string('label');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
        Schema::create('pengujian_dokumen_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dokumen_id');
            $table->unsignedBigInteger('service_parameter_id');
            $table->unsignedInteger('qty')->default(1);
            $table->boolean('is_direct')->default(false);
            $table->boolean('is_sesuai')->default(true);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
        Schema::create('kodings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permohonan_id');
            $table->string('status')->default('draft');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('koding_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('koding_id');
            $table->unsignedBigInteger('pengujian_dokumen_id');
            $table->unsignedBigInteger('pengujian_dokumen_parameter_id')->nullable();
            $table->string('kode')->nullable();
            $table->timestamps();
        });
        Schema::create('prepanalisisas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permohonan_id');
            $table->string('status')->default('draft');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('prepanalisa_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prepanalisa_id');
            $table->unsignedBigInteger('koding_item_id');
            $table->unsignedBigInteger('pengujian_dokumen_parameter_id');
            $table->unsignedBigInteger('service_parameter_id');
            $table->string('kode_koding')->nullable();
            $table->json('data_skpm')->nullable();
            $table->json('data_hasil_baca')->nullable();
            $table->json('data_hasil_perhitungan')->nullable();
            $table->unsignedInteger('assigned_user_id')->nullable();
            $table->boolean('is_done')->default(false);
            $table->string('verif_status')->default('pending');
            $table->text('verif_note')->nullable();
            $table->unsignedInteger('verif_by')->nullable();
            $table->timestamp('verif_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }
}
