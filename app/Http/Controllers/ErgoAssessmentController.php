<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ErgoAssessmentController extends Controller
{
    /**
     * Menampilkan daftar riwayat asesmen ergonomi.
     */
    public function index()
    {
        $assessments = DB::table('ergo_assessments')
            ->join('ergo_companies', 'ergo_assessments.company_id', '=', 'ergo_companies.id')
            ->leftJoin('ergo_workers', 'ergo_workers.assessment_id', '=', 'ergo_assessments.id')
            ->leftJoin('ergo_reba_scores', 'ergo_reba_scores.worker_id', '=', 'ergo_workers.id')
            ->select(
                'ergo_assessments.id',
                'ergo_assessments.assessment_date',
                'ergo_companies.name as company_name',
                'ergo_workers.name as worker_name',
                'ergo_workers.position',
                'ergo_reba_scores.final_score as total_score',
                'ergo_reba_scores.risk_level',
                DB::raw('(SELECT COUNT(*) FROM ergo_assessment_photos WHERE ergo_assessment_photos.assessment_id = ergo_assessments.id) as photos_count')
            )
            ->latest('ergo_assessments.created_at')
            ->paginate(10);

        return view('ergo.index', compact('assessments'));
    }

    /**
     * Form input asesmen baru.
     */
    public function create()
    {
        return view('ergo.create');
    }

    /**
     * Menyimpan data pengujian asesmen, profil pekerja, perhitungan skor,
     * serta multi-upload berkas foto fisik dan koordinat JSON anotasi sudut.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'worker_name'    => 'required|string|max:255',
            'position'       => 'required|string|max:255',
            'ergo_photos.*'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $shiftHours = floatval($request->input('shift_hours', 8));
            $overtimeBonus = $shiftHours > 8 ? ($shiftHours - 8) * 0.5 : 0;

            $scoreUpper = 0;
            $ergoItems = $request->input('ergo_items', []);
            foreach ($ergoItems as $no => $score) {
                if (intval($no) <= 16) {
                    $scoreUpper += intval($score);
                }
            }

            $scoreLower = 0;
            foreach ($ergoItems as $no => $score) {
                if (intval($no) > 16) {
                    $scoreLower += intval($score);
                }
            }

            $mmhWeight = intval($request->input('mmh_weight_score', 0));
            $mmhDist   = intval($request->input('mmh_distance_score', 0));
            $scoreMMH  = $mmhWeight + $mmhDist;

            $totalScore = $scoreUpper + $scoreLower + $scoreMMH + $overtimeBonus;

            $riskLevel = 'Aman';
            if ($totalScore >= 2 && $totalScore <= 6) {
                $riskLevel = 'Perlu Pengamatan Lanjut';
            } elseif ($totalScore >= 7) {
                $riskLevel = 'Berbahaya';
            }

            $company = DB::table('ergo_companies')->where('name', $request->input('company_name'))->first();
            if (!$company) {
                $companyId = DB::table('ergo_companies')->insertGetId([
                    'name'       => $request->input('company_name'),
                    'address'    => $request->input('address'),
                    'sector'     => $request->input('company_type'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $companyId = $company->id;
            }

            $assessmentId = DB::table('ergo_assessments')->insertGetId([
                'company_id'       => $companyId,
                'assessment_date'  => $request->input('assessment_date', date('Y-m-d')),
                'shift_hours'      => $shiftHours,
                'method'           => 'SNI 9011:2021',
                'existing_control' => $request->input('existing_control'),
                'surveyor_id'      => auth()->id() ?? 1,
                'department'       => $request->input('position', 'Operasional'),
                'status'           => 'submitted',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $workerId = DB::table('ergo_workers')->insertGetId([
                'assessment_id'       => $assessmentId,
                'name'                => $request->input('worker_name'),
                'position'            => $request->input('position'),
                'job_tasks'           => $request->input('job_tasks'),
                'job_duration'        => $request->input('job_duration'),
                'dominant_hand'       => $request->input('dominant_hand', 'Kanan'),
                'work_duration_level' => $request->input('work_duration_level', '1 - 5 tahun'),
                'mental_fatigue'      => $request->input('mental_fatigue', 'Tidak pernah'),
                'physical_fatigue'    => $request->input('physical_fatigue', 'Kadang-kadang'),
                'has_pain_last_year'  => $request->input('has_pain_last_year', 0),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            DB::table('ergo_reba_scores')->insert([
                'worker_id'         => $workerId,
                'upper_body_score'  => $scoreUpper,
                'lower_body_score'  => $scoreLower,
                'mmh_score'         => $scoreMMH,
                'final_score'       => $totalScore,
                'risk_level'        => $riskLevel,
                'notes'             => $request->input('existing_control'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $photosJsonData = json_decode($request->input('annotated_photos_json', '[]'), true);
            $uploadedFiles  = $request->file('ergo_photos', []);

            if (is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $index => $file) {
                    if ($file && $file->isValid()) {
                        $storedPath = $file->store('ergo-photos', 'public');
                        $landmarks = isset($photosJsonData[$index]['landmarks'])
                            ? json_encode($photosJsonData[$index]['landmarks'])
                            : null;

                        DB::table('ergo_assessment_photos')->insert([
                            'assessment_id'  => $assessmentId,
                            'photo_name'     => $file->getClientOriginalName(),
                            'file_path'      => $storedPath,
                            'landmarks_json' => $landmarks,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('ergo.index')->with('success', 'Data pengujian ergonomi dan foto berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail lengkap asesmen dan foto dokumentasi.
     */
    public function show($id)
    {
        $assessment = DB::table('ergo_assessments')
            ->join('ergo_companies', 'ergo_assessments.company_id', '=', 'ergo_companies.id')
            ->leftJoin('ergo_workers', 'ergo_workers.assessment_id', '=', 'ergo_assessments.id')
            ->leftJoin('ergo_reba_scores', 'ergo_reba_scores.worker_id', '=', 'ergo_workers.id')
            ->where('ergo_assessments.id', $id)
            ->select(
                'ergo_assessments.*',
                'ergo_companies.name as company_name',
                'ergo_companies.address as company_address',
                'ergo_companies.sector as company_sector',
                'ergo_workers.name as worker_name',
                'ergo_workers.position',
                'ergo_workers.job_tasks',
                'ergo_workers.job_duration',
                'ergo_workers.dominant_hand',
                'ergo_workers.work_duration_level',
                'ergo_workers.mental_fatigue',
                'ergo_workers.physical_fatigue',
                'ergo_workers.has_pain_last_year',
                'ergo_reba_scores.upper_body_score',
                'ergo_reba_scores.lower_body_score',
                'ergo_reba_scores.mmh_score',
                'ergo_reba_scores.final_score as total_score',
                'ergo_reba_scores.risk_level',
                'ergo_reba_scores.notes'
            )
            ->first();

        if (!$assessment) {
            return redirect()->route('ergo.index')->with('error', 'Data asesmen tidak ditemukan.');
        }

        $photos = DB::table('ergo_assessment_photos')
            ->where('assessment_id', $id)
            ->get();

        return view('ergo.result', compact('assessment', 'photos'));
    }

    /**
     * Menampilkan form edit yang identik dengan form create.
     */
    public function edit($id)
    {
        $assessment = DB::table('ergo_assessments')
            ->join('ergo_companies', 'ergo_assessments.company_id', '=', 'ergo_companies.id')
            ->leftJoin('ergo_workers', 'ergo_workers.assessment_id', '=', 'ergo_assessments.id')
            ->leftJoin('ergo_reba_scores', 'ergo_reba_scores.worker_id', '=', 'ergo_workers.id')
            ->where('ergo_assessments.id', $id)
            ->select(
                'ergo_assessments.*',
                'ergo_companies.name as company_name',
                'ergo_companies.address as company_address',
                'ergo_companies.sector as company_sector',
                'ergo_workers.id as worker_id',
                'ergo_workers.name as worker_name',
                'ergo_workers.position',
                'ergo_workers.job_tasks',
                'ergo_workers.job_duration',
                'ergo_workers.dominant_hand',
                'ergo_workers.work_duration_level',
                'ergo_workers.mental_fatigue',
                'ergo_workers.physical_fatigue',
                'ergo_workers.has_pain_last_year',
                'ergo_reba_scores.upper_body_score',
                'ergo_reba_scores.lower_body_score',
                'ergo_reba_scores.mmh_score',
                'ergo_reba_scores.final_score as total_score',
                'ergo_reba_scores.risk_level'
            )
            ->first();

        if (!$assessment) {
            return redirect()->route('ergo.index')->with('error', 'Data asesmen tidak ditemukan.');
        }

        $photos = DB::table('ergo_assessment_photos')
            ->where('assessment_id', $id)
            ->get();

        return view('ergo.edit', compact('assessment', 'photos'));
    }

    /**
     * Memperbarui data pengujian asesmen.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'worker_name'    => 'required|string|max:255',
            'position'       => 'required|string|max:255',
            'ergo_photos.*'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $assessment = DB::table('ergo_assessments')->where('id', $id)->first();
            if (!$assessment) {
                return redirect()->route('ergo.index')->with('error', 'Data asesmen tidak ditemukan.');
            }

            $shiftHours = floatval($request->input('shift_hours', 8));
            $overtimeBonus = $shiftHours > 8 ? ($shiftHours - 8) * 0.5 : 0;

            $scoreUpper = 0;
            $ergoItems = $request->input('ergo_items', []);
            foreach ($ergoItems as $no => $score) {
                if (intval($no) <= 16) {
                    $scoreUpper += intval($score);
                }
            }

            $scoreLower = 0;
            foreach ($ergoItems as $no => $score) {
                if (intval($no) > 16) {
                    $scoreLower += intval($score);
                }
            }

            $mmhWeight = intval($request->input('mmh_weight_score', 0));
            $mmhDist   = intval($request->input('mmh_distance_score', 0));
            $scoreMMH  = $mmhWeight + $mmhDist;

            $totalScore = $scoreUpper + $scoreLower + $scoreMMH + $overtimeBonus;

            $riskLevel = 'Aman';
            if ($totalScore >= 2 && $totalScore <= 6) {
                $riskLevel = 'Perlu Pengamatan Lanjut';
            } elseif ($totalScore >= 7) {
                $riskLevel = 'Berbahaya';
            }

            DB::table('ergo_companies')->where('id', $assessment->company_id)->update([
                'name'       => $request->input('company_name'),
                'address'    => $request->input('address'),
                'sector'     => $request->input('company_type'),
                'updated_at' => now(),
            ]);

            DB::table('ergo_assessments')->where('id', $id)->update([
                'assessment_date'  => $request->input('assessment_date', date('Y-m-d')),
                'shift_hours'      => $shiftHours,
                'existing_control' => $request->input('existing_control'),
                'updated_at'       => now(),
            ]);

            DB::table('ergo_workers')->where('assessment_id', $id)->update([
                'name'                => $request->input('worker_name'),
                'position'            => $request->input('position'),
                'job_tasks'           => $request->input('job_tasks'),
                'job_duration'        => $request->input('job_duration'),
                'dominant_hand'       => $request->input('dominant_hand', 'Kanan'),
                'work_duration_level' => $request->input('work_duration_level', '1 - 5 tahun'),
                'mental_fatigue'      => $request->input('mental_fatigue', 'Tidak pernah'),
                'physical_fatigue'    => $request->input('physical_fatigue', 'Kadang-kadang'),
                'has_pain_last_year'  => $request->input('has_pain_last_year', 0),
                'updated_at'          => now(),
            ]);

            $worker = DB::table('ergo_workers')->where('assessment_id', $id)->first();
            if ($worker) {
                DB::table('ergo_reba_scores')->where('worker_id', $worker->id)->update([
                    'upper_body_score' => $scoreUpper,
                    'lower_body_score' => $scoreLower,
                    'mmh_score'        => $scoreMMH,
                    'final_score'      => $totalScore,
                    'risk_level'       => $riskLevel,
                    'notes'            => $request->input('existing_control'),
                    'updated_at'       => now(),
                ]);
            }

            $uploadedFiles = $request->file('ergo_photos', []);
            if (is_array($uploadedFiles) && count($uploadedFiles) > 0) {
                $oldPhotos = DB::table('ergo_assessment_photos')->where('assessment_id', $id)->get();
                foreach ($oldPhotos as $old) {
                    if ($old->file_path && Storage::disk('public')->exists($old->file_path)) {
                        Storage::disk('public')->delete($old->file_path);
                    }
                }
                DB::table('ergo_assessment_photos')->where('assessment_id', $id)->delete();

                $photosJsonData = json_decode($request->input('annotated_photos_json', '[]'), true);
                foreach ($uploadedFiles as $index => $file) {
                    if ($file && $file->isValid()) {
                        $storedPath = $file->store('ergo-photos', 'public');
                        $landmarks = isset($photosJsonData[$index]['landmarks']) ? json_encode($photosJsonData[$index]['landmarks']) : null;

                        DB::table('ergo_assessment_photos')->insert([
                            'assessment_id'  => $id,
                            'photo_name'     => $file->getClientOriginalName(),
                            'file_path'      => $storedPath,
                            'landmarks_json' => $landmarks,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('ergo.result', $id)->with('success', 'Data pengujian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus asesmen beserta file fisiknya dari storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $photos = DB::table('ergo_assessment_photos')->where('assessment_id', $id)->get();
            foreach ($photos as $photo) {
                if ($photo->file_path && Storage::disk('public')->exists($photo->file_path)) {
                    Storage::disk('public')->delete($photo->file_path);
                }
            }

            DB::table('ergo_assessment_photos')->where('assessment_id', $id)->delete();

            $worker = DB::table('ergo_workers')->where('assessment_id', $id)->first();
            if ($worker) {
                DB::table('ergo_reba_scores')->where('worker_id', $worker->id)->delete();
                DB::table('ergo_workers')->where('id', $worker->id)->delete();
            }

            DB::table('ergo_assessments')->where('id', $id)->delete();

            DB::commit();
            return redirect()->route('ergo.index')->with('success', 'Data pengujian beserta berkas foto berhasil dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('ergo.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Mengekspor dokumen LHU resmi berstandar Balai K3 Surabaya (PDF).
     */
    public function exportPdf($id)
    {
        $assessment = DB::table('ergo_assessments')
            ->join('ergo_companies', 'ergo_assessments.company_id', '=', 'ergo_companies.id')
            ->leftJoin('ergo_workers', 'ergo_workers.assessment_id', '=', 'ergo_assessments.id')
            ->leftJoin('ergo_reba_scores', 'ergo_reba_scores.worker_id', '=', 'ergo_workers.id')
            ->where('ergo_assessments.id', $id)
            ->select(
                'ergo_assessments.*',
                'ergo_companies.name as company_name',
                'ergo_companies.address as company_address',
                'ergo_companies.sector as company_sector',
                'ergo_workers.name as worker_name',
                'ergo_workers.position',
                'ergo_workers.job_tasks',
                'ergo_workers.job_duration',
                'ergo_workers.dominant_hand',
                'ergo_workers.work_duration_level',
                'ergo_workers.mental_fatigue',
                'ergo_workers.physical_fatigue',
                'ergo_workers.has_pain_last_year', // Pastikan kolom ini di-select
                'ergo_reba_scores.upper_body_score',
                'ergo_reba_scores.lower_body_score',
                'ergo_reba_scores.mmh_score',
                'ergo_reba_scores.final_score as total_score',
                'ergo_reba_scores.risk_level',
                'ergo_reba_scores.notes'
            )
            ->first();

        if (!$assessment) {
            return redirect()->route('ergo.index')->with('error', 'Data pengujian tidak ditemukan.');
        }

        // Encode berkas foto ke Base64 agar dapat di-render DomPDF secara lokal
        $rawPhotos = DB::table('ergo_assessment_photos')
            ->where('assessment_id', $id)
            ->get();

        $photos = [];
        foreach ($rawPhotos as $photo) {
            $path = storage_path('app/public/' . $photo->file_path);
            $base64 = null;
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            $photos[] = [
                'name'   => $photo->photo_name,
                'base64' => $base64,
            ];
        }

        $pdf = Pdf::loadView('ergo.pdf', compact('assessment', 'photos'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $fileName = 'LHU-ERGONOMI-' . Str::slug($assessment->company_name) . '-' . Str::slug($assessment->worker_name) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Menampilkan form live editor untuk narasi dan metadata LHU resmi.
     */
    public function editLhu($id)
    {
        $assessment = DB::table('ergo_assessments')
            ->join('ergo_companies', 'ergo_assessments.company_id', '=', 'ergo_companies.id')
            ->leftJoin('ergo_workers', 'ergo_workers.assessment_id', '=', 'ergo_assessments.id')
            ->leftJoin('ergo_reba_scores', 'ergo_reba_scores.worker_id', '=', 'ergo_workers.id')
            ->where('ergo_assessments.id', $id)
            ->select(
                'ergo_assessments.*',
                'ergo_companies.name as company_name',
                'ergo_companies.address as company_address',
                'ergo_companies.sector as company_sector',
                'ergo_workers.name as worker_name',
                'ergo_workers.position',
                'ergo_reba_scores.upper_body_score',
                'ergo_reba_scores.lower_body_score',
                'ergo_reba_scores.mmh_score',
                'ergo_reba_scores.final_score as total_score',
                'ergo_reba_scores.risk_level'
            )
            ->first();

        if (!$assessment) {
            return redirect()->route('ergo.index')->with('error', 'Data pengujian tidak ditemukan.');
        }

        return view('ergo.lhu-editor', compact('assessment'));
    }

    /**
     * Memperbarui narasi dan metadata LHU resmi.
     */
    public function updateLhu(Request $request, $id)
    {
        $request->validate([
            'lhu_doc_number'     => 'nullable|string|max:255',
            'company_pic'        => 'nullable|string|max:255',
            'lhu_analysis'       => 'nullable|string',
            'lhu_conclusion'     => 'nullable|string',
            'lhu_recommendation' => 'nullable|string',
            'signer_name'        => 'nullable|string|max:255',
            'signer_nip'         => 'nullable|string|max:255',
            'signer_position'    => 'nullable|string|max:255',
        ]);

        try {
            DB::table('ergo_assessments')->where('id', $id)->update([
                'lhu_doc_number'     => $request->input('lhu_doc_number'),
                'company_pic'        => $request->input('company_pic'),
                'lhu_analysis'       => $request->input('lhu_analysis'),
                'lhu_conclusion'     => $request->input('lhu_conclusion'),
                'lhu_recommendation' => $request->input('lhu_recommendation'),
                'signer_name'        => $request->input('signer_name'),
                'signer_nip'         => $request->input('signer_nip'),
                'signer_position'    => $request->input('signer_position'),
                'updated_at'         => now(),
            ]);

            return redirect()->route('ergo.lhu.edit', $id)->with('success', 'Draf LHU resmi berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui narasi LHU: ' . $e->getMessage());
        }
    }
}
