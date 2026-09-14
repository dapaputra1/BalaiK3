<?php

namespace App\Http\Controllers;

use App\Models\JejaringEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SuperadminJejaringController extends Controller
{
    public function index()
    {
        $tableReady = $this->hasJejaringTable();

        $entries = $tableReady
            ? JejaringEntry::query()
                ->with('uploader')
                ->orderByRaw($this->categoryOrderCase())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        $countsByCategory = $entries
            ->groupBy('category')
            ->map(fn ($items) => $items->count());

        return view('admin.superadmin_jejaring', [
            'entries' => $entries,
            'categories' => JejaringEntry::categoryDefinitions(),
            'countsByCategory' => $countsByCategory,
            'tableReady' => $tableReady,
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->hasJejaringTable()) {
            return redirect()
                ->route('superadmin.jejaring.index')
                ->withErrors(['jejaring' => 'Tabel jejaring belum tersedia. Jalankan php artisan migrate terlebih dahulu.']);
        }

        $data = $this->validateData($request);

        JejaringEntry::create([
            'user_id' => auth()->id(),
            'category' => $data['category'],
            'name' => $data['name'],
            'address' => $data['address'] ?: null,
            'website' => $data['website'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()
            ->route('superadmin.jejaring.index')
            ->with('success', 'Data jejaring berhasil ditambahkan.');
    }

    public function update(Request $request, JejaringEntry $jejaringEntry)
    {
        if (!$this->hasJejaringTable()) {
            return redirect()
                ->route('superadmin.jejaring.index')
                ->withErrors(['jejaring' => 'Tabel jejaring belum tersedia. Jalankan php artisan migrate terlebih dahulu.']);
        }

        $data = $this->validateData($request, $jejaringEntry);

        $jejaringEntry->update([
            'user_id' => auth()->id(),
            'category' => $data['category'],
            'name' => $data['name'],
            'address' => $data['address'] ?: null,
            'website' => $data['website'] ?: null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()
            ->route('superadmin.jejaring.index')
            ->with('success', 'Data jejaring berhasil diperbarui.');
    }

    public function destroy(JejaringEntry $jejaringEntry)
    {
        if (!$this->hasJejaringTable()) {
            return redirect()
                ->route('superadmin.jejaring.index')
                ->withErrors(['jejaring' => 'Tabel jejaring belum tersedia. Jalankan php artisan migrate terlebih dahulu.']);
        }

        $jejaringEntry->delete();

        return redirect()
            ->route('superadmin.jejaring.index')
            ->with('success', 'Data jejaring berhasil dihapus.');
    }

    private function validateData(Request $request, ?JejaringEntry $jejaringEntry = null): array
    {
        $request->merge([
            'website' => $this->normalizeUrlInput($request->input('website')),
        ]);

        $category = (string) $request->input('category');

        return $request->validate([
            'category' => ['required', 'string', Rule::in(JejaringEntry::categoryKeys())],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('jejaring_entries', 'name')
                    ->where(fn ($query) => $query->where('category', $category))
                    ->ignore($jejaringEntry?->id),
            ],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'category' => 'kategori jejaring',
            'name' => 'nama',
            'address' => 'alamat',
            'website' => 'website',
            'sort_order' => 'urutan tampil',
            'is_active' => 'status',
        ]);
    }

    private function normalizeUrlInput(?string $url): ?string
    {
        $normalized = trim((string) $url);
        if ($normalized === '') {
            return null;
        }

        if (!preg_match('~^https?://~i', $normalized)) {
            $normalized = 'https://' . ltrim($normalized, '/');
        }

        return $normalized;
    }

    private function categoryOrderCase(): string
    {
        $cases = collect(JejaringEntry::categoryKeys())
            ->values()
            ->map(fn (string $category, int $index) => "WHEN '{$category}' THEN {$index}")
            ->implode(' ');

        return "CASE category {$cases} ELSE 999 END";
    }

    private function hasJejaringTable(): bool
    {
        return Schema::hasTable('jejaring_entries');
    }
}
