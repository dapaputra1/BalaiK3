<?php

namespace App\Http\Controllers;

use App\Models\AbsorbanceFormula;
use App\Services\FormulaReference\AbsorbanceReferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SuperadminAbsorbansiController extends Controller
{
    private const PARAMETER_OPTIONS = [
        'NO2' => 'NO2',
        'OX' => 'OX',
        'PB' => 'Pb',
        'CD' => 'Cd',
        'CR' => 'Cr',
        'AS' => 'As',
        'HG' => 'Hg',
        'CO' => 'Co',
        'SB' => 'Sb',
        'TL' => 'Tl',
        'CU' => 'Cu',
        'ZN' => 'Zn',
        'BENZENE' => 'Benzene',
        'TOLUENE' => 'Toluene',
        'XYLENE' => 'Xylene',
    ];

    public function index()
    {
        $rows = AbsorbanceFormula::query()
            ->orderBy('parameter_key')
            ->orderByDesc('effective_date')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.superadmin_manageabsorbansi', [
            'rows' => $rows,
            'parameterOptions' => self::PARAMETER_OPTIONS,
        ]);
    }

    public function reference(string $parameter, AbsorbanceReferenceService $referenceService): JsonResponse
    {
        $parameterKey = strtoupper(trim($parameter));

        if (!array_key_exists($parameterKey, self::PARAMETER_OPTIONS)) {
            return response()->json([
                'message' => 'Parameter rumus tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'reference' => $referenceService->getReference($parameterKey),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data) {
            if ((bool) ($data['is_active'] ?? false)) {
                AbsorbanceFormula::query()
                    ->where('parameter_key', $data['parameter_key'])
                    ->update(['is_active' => false]);
            }

            AbsorbanceFormula::create($data);
        });

        $this->refreshPrepanalisaFormulaCaches();

        return redirect()
            ->route('superadmin.absorbansi.index')
            ->with('success', 'Rumus berhasil ditambahkan.');
    }

    public function update(Request $request, AbsorbanceFormula $absorbansi)
    {
        $data = $this->validateData($request, $absorbansi);

        DB::transaction(function () use ($data, $absorbansi) {
            if ((bool) ($data['is_active'] ?? false)) {
                AbsorbanceFormula::query()
                    ->where('parameter_key', $data['parameter_key'])
                    ->whereKeyNot($absorbansi->id)
                    ->update(['is_active' => false]);
            }

            $absorbansi->update($data);
        });

        $this->refreshPrepanalisaFormulaCaches();

        return redirect()
            ->route('superadmin.absorbansi.index')
            ->with('success', 'Rumus berhasil diperbarui.');
    }

    public function destroy(Request $request, AbsorbanceFormula $absorbansi)
    {
        if (!$request->boolean('confirm')) {
            return redirect()
                ->route('superadmin.absorbansi.index')
                ->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $absorbansi->delete();
        $this->refreshPrepanalisaFormulaCaches();

        return redirect()
            ->route('superadmin.absorbansi.index')
            ->with('success', 'Rumus berhasil dihapus.');
    }

    private function validateData(Request $request, ?AbsorbanceFormula $row = null): array
    {
        $parameterKey = strtoupper((string) $request->input('parameter_key'));
        $isBtxFormula = in_array($parameterKey, ['BENZENE', 'TOLUENE', 'XYLENE'], true);
        $isCurveFormula = in_array($parameterKey, ['PB', 'CD', 'CR', 'AS', 'HG', 'CO', 'SB', 'TL', 'CU', 'ZN'], true);

        $validated = $request->validate([
            'parameter_key' => [
                'required',
                'string',
                Rule::in(array_keys(self::PARAMETER_OPTIONS)),
            ],
            'intercept' => ['required', 'numeric'],
            'slope' => [
                Rule::requiredIf(!$isBtxFormula),
                'nullable',
                'numeric',
            ],
            'effective_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'parameter_key' => 'parameter',
            'intercept' => ($isBtxFormula || $isCurveFormula) ? 'nilai Y' : 'nilai dasar',
            'slope' => ($isBtxFormula || $isCurveFormula) ? 'nilai X' : 'pengali absorbansi',
            'effective_date' => 'tanggal berlaku',
            'notes' => 'catatan',
        ]);

        $validated['parameter_key'] = $parameterKey;

        if ($isBtxFormula) {
            $yValue = (float) $validated['intercept'];
            if ($yValue == 0.0) {
                throw ValidationException::withMessages([
                    'intercept' => 'Nilai Y tidak boleh 0.',
                ]);
            }

            $validated['slope'] = 1 / $yValue;
        }

        if ($isCurveFormula) {
            $yValue = (float) ($validated['intercept'] ?? 0.0);
            $xValue = $validated['slope'] === null || $validated['slope'] === ''
                ? 0.0
                : (float) $validated['slope'];

            if ($yValue == 0.0 && $xValue == 0.0) {
                throw ValidationException::withMessages([
                    'intercept' => 'Nilai Y atau Nilai X harus diisi.',
                ]);
            }

            if ($yValue == 0.0 && $xValue != 0.0) {
                $yValue = 1 / $xValue;
            }

            if ($xValue == 0.0 && $yValue != 0.0) {
                $xValue = 1 / $yValue;
            }

            if ($yValue == 0.0 || $xValue == 0.0) {
                throw ValidationException::withMessages([
                    'intercept' => 'Nilai Y dan Nilai X tidak boleh 0.',
                ]);
            }

            $validated['intercept'] = $yValue;
            $validated['slope'] = $xValue;
        }

        return $validated;
    }

    private function refreshPrepanalisaFormulaCaches(): void
    {
        foreach ([
            'prepanalisa:formula-references:v1',
            'prepanalisa:formula-references:v2',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }
}
