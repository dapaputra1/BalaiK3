<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\ServicePackageItem;
use App\Models\ServiceParameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperadminServiceParameterController extends Controller
{
    public function index()
    {
        $categories = ServiceCategory::orderBy('name')->get();
        $parameters = ServiceParameter::with('category')
            ->orderBy('name')
            ->get();
        $emisiCategory = ServiceCategory::query()->where('name', 'Emisi')->first();
        $emisiParameters = collect();
        $packages = collect();

        if ($emisiCategory) {
            $emisiParameters = ServiceParameter::query()
                ->where('service_category_id', $emisiCategory->id)
                ->orderBy('name')
                ->get();

            $packages = ServicePackage::query()
                ->with(['items.parameter'])
                ->where('service_category_id', $emisiCategory->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('admin.superadmin_manageparameters', [
            'categories' => $categories,
            'parameters' => $parameters,
            'emisiCategory' => $emisiCategory,
            'emisiParameters' => $emisiParameters,
            'packages' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'short_code' => ['required', 'string', 'max:30', 'unique:service_parameters,short_code'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ServiceParameter::create([
            'service_category_id' => $data['service_category_id'],
            'name' => $data['name'],
            'short_code' => $data['short_code'],
            'price' => $data['price'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Parameter berhasil ditambahkan.');
    }

    public function update(Request $request, ServiceParameter $service_parameter)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'short_code' => ['required', 'string', 'max:30', 'unique:service_parameters,short_code,' . $service_parameter->id],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service_parameter->update([
            'service_category_id' => $data['service_category_id'],
            'name' => $data['name'],
            'short_code' => $data['short_code'],
            'price' => $data['price'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Parameter berhasil diperbarui.');
    }

    public function destroy(Request $request, ServiceParameter $service_parameter)
    {
        if (!$request->boolean('confirm')) {
            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $usages = [
            'keranjang' => $service_parameter->cartItems()->count(),
            'order' => $service_parameter->orderItems()->count(),
            'permohonan' => $service_parameter->permohonanParameters()->count(),
            'dokumen pengujian' => $service_parameter->pengujianDokumenParameters()->count(),
            'prepanalisa' => $service_parameter->prepanalisaItems()->count(),
            'parameter lod' => $service_parameter->parameterLod()->count(),
        ];

        $activeUsages = array_filter($usages);

        if ($activeUsages !== []) {
            $usageText = collect($activeUsages)
                ->map(fn (int $count, string $label) => $label . ' (' . $count . ')')
                ->implode(', ');

            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors([
                    'hapus' => 'Parameter tidak bisa dihapus karena masih digunakan pada: ' . $usageText . '.',
                ]);
        }

        try {
            $service_parameter->delete();
        } catch (QueryException $exception) {
            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors([
                    'hapus' => 'Parameter gagal dihapus karena masih memiliki relasi data lain.',
                ]);
        }

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Parameter berhasil dihapus.');
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'short_code' => ['required', 'string', 'max:40', 'unique:service_packages,short_code'],
            'badge' => ['nullable', 'string', 'max:40'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'item_parameter_ids' => ['array'],
            'item_parameter_ids.*' => ['nullable', 'integer', 'exists:service_parameters,id'],
            'item_labels' => ['array'],
            'item_labels.*' => ['nullable', 'string', 'max:150'],
        ]);

        $parameterIds = collect($data['item_parameter_ids'] ?? [])->values();
        $labels = collect($data['item_labels'] ?? [])->values();

        DB::transaction(function () use ($data, $parameterIds, $labels) {
            $package = ServicePackage::create([
                'service_category_id' => $data['service_category_id'],
                'name' => $data['name'],
                'short_code' => strtoupper(trim($data['short_code'])),
                'badge' => $data['badge'] ?? null,
                'subtitle' => $data['subtitle'] ?? null,
                'price' => $data['price'],
                'unit' => $data['unit'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $max = max($parameterIds->count(), $labels->count());
            for ($i = 0; $i < $max; $i++) {
                $label = trim((string) ($labels[$i] ?? ''));
                $parameterId = $parameterIds[$i] ?? null;
                $parameterId = is_numeric($parameterId) ? (int) $parameterId : null;

                if (!$parameterId && $label === '') {
                    continue;
                }

                ServicePackageItem::create([
                    'service_package_id' => $package->id,
                    'service_parameter_id' => $parameterId ?: null,
                    'label' => $label !== '' ? $label : null,
                    'sort_order' => $i + 1,
                ]);
            }
        });

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Paket emisi berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, ServicePackage $service_package)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'short_code' => ['required', 'string', 'max:40', 'unique:service_packages,short_code,' . $service_package->id],
            'badge' => ['nullable', 'string', 'max:40'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'item_parameter_ids' => ['array'],
            'item_parameter_ids.*' => ['nullable', 'integer', 'exists:service_parameters,id'],
            'item_labels' => ['array'],
            'item_labels.*' => ['nullable', 'string', 'max:150'],
        ]);

        $parameterIds = collect($data['item_parameter_ids'] ?? [])->values();
        $labels = collect($data['item_labels'] ?? [])->values();

        DB::transaction(function () use ($service_package, $data, $parameterIds, $labels) {
            $service_package->update([
                'service_category_id' => $data['service_category_id'],
                'name' => $data['name'],
                'short_code' => strtoupper(trim($data['short_code'])),
                'badge' => $data['badge'] ?? null,
                'subtitle' => $data['subtitle'] ?? null,
                'price' => $data['price'],
                'unit' => $data['unit'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $service_package->items()->delete();

            $max = max($parameterIds->count(), $labels->count());
            for ($i = 0; $i < $max; $i++) {
                $label = trim((string) ($labels[$i] ?? ''));
                $parameterId = $parameterIds[$i] ?? null;
                $parameterId = is_numeric($parameterId) ? (int) $parameterId : null;

                if (!$parameterId && $label === '') {
                    continue;
                }

                ServicePackageItem::create([
                    'service_package_id' => $service_package->id,
                    'service_parameter_id' => $parameterId ?: null,
                    'label' => $label !== '' ? $label : null,
                    'sort_order' => $i + 1,
                ]);
            }
        });

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Paket emisi berhasil diperbarui.');
    }

    public function destroyPackage(Request $request, ServicePackage $service_package)
    {
        if (!$request->boolean('confirm')) {
            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        try {
            $service_package->delete();
        } catch (QueryException $exception) {
            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors(['hapus' => 'Paket gagal dihapus karena masih digunakan.']);
        }

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Paket emisi berhasil dihapus.');
    }
}
