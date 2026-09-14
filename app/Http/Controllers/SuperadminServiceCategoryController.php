<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class SuperadminServiceCategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'short_code' => ['required', 'string', 'max:20', 'unique:service_categories,short_code'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ServiceCategory::create([
            'name' => $data['name'],
            'short_code' => $data['short_code'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, ServiceCategory $service_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'short_code' => ['required', 'string', 'max:20', 'unique:service_categories,short_code,' . $service_category->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $service_category->update([
            'name' => $data['name'],
            'short_code' => $data['short_code'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, ServiceCategory $service_category)
    {
        if (!$request->boolean('confirm')) {
            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        if ($service_category->parameters()->exists()) {
            return redirect()->route('superadmin.service-parameters.index')
                ->withErrors(['hapus' => 'Kategori tidak bisa dihapus karena masih memiliki parameter.']);
        }

        $service_category->delete();

        return redirect()->route('superadmin.service-parameters.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
