<?php

namespace App\Http\Controllers;

use App\Models\ParameterLod;
use App\Models\ServiceParameter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuperadminParameterLodController extends Controller
{
    private const DEFAULT_FACTOR_PPM = 0.382;
    private const DEFAULT_FACTOR_UGM3 = 2617.6;

    public function index()
    {
        $parameters = ServiceParameter::query()
            ->with(['category', 'parameterLod'])
            ->orderBy('name')
            ->get();

        $lods = ParameterLod::query()
            ->with('serviceParameter.category')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.superadmin_manageparameterlods', [
            'parameters' => $parameters,
            'lods' => $lods,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        ParameterLod::create($data);

        return redirect()
            ->route('superadmin.parameter-lods.index')
            ->with('success', 'LOD parameter berhasil ditambahkan.');
    }

    public function update(Request $request, ParameterLod $parameter_lod)
    {
        $data = $this->validateData($request, $parameter_lod);

        $parameter_lod->update($data);

        return redirect()
            ->route('superadmin.parameter-lods.index')
            ->with('success', 'LOD parameter berhasil diperbarui.');
    }

    public function destroy(Request $request, ParameterLod $parameter_lod)
    {
        if (!$request->boolean('confirm')) {
            return redirect()
                ->route('superadmin.parameter-lods.index')
                ->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $parameter_lod->delete();

        return redirect()
            ->route('superadmin.parameter-lods.index')
            ->with('success', 'LOD parameter berhasil dihapus.');
    }

    private function validateData(Request $request, ?ParameterLod $parameterLod = null): array
    {
        $serviceParameterId = (int) $request->input('service_parameter_id');
        $isDebuParameter = false;
        if ($serviceParameterId > 0) {
            $parameterName = (string) ServiceParameter::query()
                ->whereKey($serviceParameterId)
                ->value('name');
            $isDebuParameter = str_contains(strtolower($parameterName), 'debu');
        }

        $data = $request->validate([
            'service_parameter_id' => [
                'required',
                'exists:service_parameters,id',
                Rule::unique('parameter_lods', 'service_parameter_id')->ignore($parameterLod?->id),
            ],
            'kons' => ['required', 'numeric', 'min:0'],
            'vol' => [$isDebuParameter ? 'nullable' : 'required', 'numeric', 'min:0'],
            'waktu' => ['required', 'numeric', 'min:0'],
            'fr' => ['required', 'numeric', 'min:0'],
            'sk' => ['required', 'numeric', 'min:0'],
            'pm' => ['required', 'numeric', 'min:0'],
            'factor_ppm' => ['nullable', 'numeric', 'min:0'],
            'factor_ugm3' => ['nullable', 'numeric', 'min:0'],
            'sample_kons' => ['nullable', 'numeric', 'min:0'],
            'sample_vol' => ['nullable', 'numeric', 'min:0'],
            'sample_waktu' => ['nullable', 'numeric', 'min:0'],
            'sample_fr' => ['nullable', 'numeric', 'min:0'],
            'sample_sk' => ['nullable', 'numeric', 'min:0'],
            'sample_pm' => ['nullable', 'numeric', 'min:0'],
            'sample_ppm' => ['nullable', 'numeric', 'min:0'],
            'sample_ugm3' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'service_parameter_id' => 'parameter',
            'kons' => 'konsentrasi LOD',
            'vol' => 'volume LOD',
            'waktu' => 'waktu LOD',
            'fr' => 'flow rate LOD',
            'sk' => 'suhu LOD',
            'pm' => 'tekanan LOD',
            'factor_ppm' => 'faktor ppm',
            'factor_ugm3' => 'faktor ug/m3',
            'sample_kons' => 'konsentrasi sampel',
            'sample_vol' => 'volume sampel',
            'sample_waktu' => 'waktu sampel',
            'sample_fr' => 'flow rate sampel',
            'sample_sk' => 'suhu sampel',
            'sample_pm' => 'tekanan sampel',
            'sample_ppm' => 'ppm sampel',
            'sample_ugm3' => 'ug/m3 sampel',
            'notes' => 'catatan',
        ]);

        // Field faktor tidak lagi ditampilkan di modal, jadi isi otomatis.
        $data['factor_ppm'] = isset($data['factor_ppm'])
            ? (float) $data['factor_ppm']
            : (float) ($parameterLod?->factor_ppm ?? self::DEFAULT_FACTOR_PPM);

        $data['factor_ugm3'] = isset($data['factor_ugm3'])
            ? (float) $data['factor_ugm3']
            : (float) ($parameterLod?->factor_ugm3 ?? self::DEFAULT_FACTOR_UGM3);

        if ($isDebuParameter && !isset($data['vol'])) {
            $data['vol'] = 0;
        }

        return $data;
    }
}
