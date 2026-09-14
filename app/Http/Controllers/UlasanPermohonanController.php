<?php

namespace App\Http\Controllers;

use App\Models\UlasanPermohonanQuestion;
use App\Models\UlasanPermohonanResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UlasanPermohonanController extends Controller
{
    public function index(Request $request)
    {
        $questions = UlasanPermohonanQuestion::query()
            ->withCount([
                'responses as total_responses',
                'responses as rating_responses_count' => function ($query) {
                    $query->whereNotNull('rating_value');
                },
                'responses as text_responses_count' => function ($query) {
                    $query->whereNotNull('text_answer')->where('text_answer', '!=', '');
                },
            ])
            ->withAvg('responses as avg_rating', 'rating_value')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $ratingRatios = UlasanPermohonanResponse::query()
            ->join('ulasan_permohonan_questions as q', 'q.id', '=', 'ulasan_permohonan_responses.question_id')
            ->where('q.type', 'rating')
            ->whereIn('q.category', ['ikm', 'ikk'])
            ->whereNotNull('ulasan_permohonan_responses.rating_value')
            ->groupBy('q.category')
            ->select('q.category', DB::raw('AVG(ulasan_permohonan_responses.rating_value) as avg_rating'))
            ->pluck('avg_rating', 'q.category');

        $ikmAvg = $ratingRatios->has('ikm') ? (float) $ratingRatios->get('ikm') : null;
        $ikkAvg = $ratingRatios->has('ikk') ? (float) $ratingRatios->get('ikk') : null;

        $recentSubmissionKeys = UlasanPermohonanResponse::query()
            ->whereNotNull('permohonan_id')
            ->select('permohonan_id', 'user_id', DB::raw('MAX(created_at) as submitted_at'))
            ->groupBy('permohonan_id', 'user_id')
            ->orderByDesc('submitted_at')
            ->limit(300)
            ->get();

        $recentSubmissions = collect();

        if ($recentSubmissionKeys->isNotEmpty()) {
            $responses = UlasanPermohonanResponse::query()
                ->with(['question', 'permohonan', 'user'])
                ->where(function ($query) use ($recentSubmissionKeys) {
                    foreach ($recentSubmissionKeys as $row) {
                        $query->orWhere(function ($inner) use ($row) {
                            $inner->where('permohonan_id', $row->permohonan_id)
                                ->where('user_id', $row->user_id);
                        });
                    }
                })
                ->get()
                ->groupBy(fn (UlasanPermohonanResponse $response) => $response->permohonan_id . ':' . $response->user_id);

            $recentSubmissions = $recentSubmissionKeys->map(function ($row) use ($responses) {
                $key = $row->permohonan_id . ':' . $row->user_id;
                $submissionResponses = $responses->get($key, collect())
                    ->sort(function (UlasanPermohonanResponse $a, UlasanPermohonanResponse $b) {
                        $orderA = (int) ($a->question?->sort_order ?? 9999);
                        $orderB = (int) ($b->question?->sort_order ?? 9999);
                        if ($orderA === $orderB) {
                            $idA = (int) ($a->question?->id ?? 9999);
                            $idB = (int) ($b->question?->id ?? 9999);
                            return $idA <=> $idB;
                        }
                        return $orderA <=> $orderB;
                    })
                    ->values();

                $first = $submissionResponses->first();
                $ikmAvg = $submissionResponses
                    ->filter(fn (UlasanPermohonanResponse $item) => $item->question?->category === 'ikm' && !is_null($item->rating_value))
                    ->avg('rating_value');
                $ikkAvg = $submissionResponses
                    ->filter(fn (UlasanPermohonanResponse $item) => $item->question?->category === 'ikk' && !is_null($item->rating_value))
                    ->avg('rating_value');

                return [
                    'submitted_at' => $row->submitted_at,
                    'permohonan_kode' => $first?->permohonan?->kode ?? '-',
                    'user_name' => $first?->user?->name ?? '-',
                    'total_answers' => $submissionResponses->count(),
                    'ikm_ratio' => !is_null($ikmAvg) ? ((float) $ikmAvg / 4) * 100 : null,
                    'ikk_ratio' => !is_null($ikkAvg) ? ((float) $ikkAvg / 4) * 100 : null,
                    'answers' => $submissionResponses->map(function (UlasanPermohonanResponse $item) {
                        $ratingValue = is_null($item->rating_value) ? null : (int) $item->rating_value;
                        $ratingLabel = null;
                        if (!is_null($ratingValue) && $ratingValue >= 1) {
                            $ratingLabel = collect($item->question?->rating_labels ?? [])->values()->get($ratingValue - 1);
                        }

                        return [
                            'no' => (int) ($item->question?->sort_order ?? 0),
                            'category' => strtoupper((string) ($item->question?->category ?? 'ikm')),
                            'question' => (string) ($item->question?->question ?? '-'),
                            'answer' => !is_null($ratingValue)
                                ? ($ratingValue . '/4' . ($ratingLabel ? ' - ' . $ratingLabel : ''))
                                : ((string) ($item->text_answer ?: '-')),
                        ];
                    })->values()->all(),
                ];
            })->values();
        }

        $activeQuestionCount = $questions->where('is_active', true)->count();
        $totalResponseCount = $questions->sum('total_responses');

        return view('admin.superadmin_ulasan_permohonan', [
            'questions' => $questions,
            'recentSubmissions' => $recentSubmissions,
            'routePrefix' => $this->routePrefix($request),
            'stats' => [
                'total_questions' => $questions->count(),
                'active_questions' => $activeQuestionCount,
                'total_responses' => $totalResponseCount,
                'ikm_avg' => $ikmAvg,
                'ikm_ratio' => !is_null($ikmAvg) ? ($ikmAvg / 4) * 100 : null,
                'ikk_avg' => $ikkAvg,
                'ikk_ratio' => !is_null($ikkAvg) ? ($ikkAvg / 4) * 100 : null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:rating,text'],
            'category' => ['required', 'in:ikm,ikk'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
            'rating_labels_input' => ['nullable', 'string'],
        ]);

        $ratingLabels = $this->parseRatingLabels($data['type'], $data['rating_labels_input'] ?? null);

        UlasanPermohonanQuestion::create([
            'question' => $data['question'],
            'type' => $data['type'],
            'category' => $data['category'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => $data['sort_order'],
            'note' => $data['note'] ?? null,
            'rating_labels' => $ratingLabels,
        ]);

        return redirect()->route($this->routePrefix($request) . '.ulasan-permohonan.index')
            ->with('success', 'Pertanyaan ulasan berhasil ditambahkan.');
    }

    public function update(Request $request, UlasanPermohonanQuestion $ulasanPermohonanQuestion)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:rating,text'],
            'category' => ['required', 'in:ikm,ikk'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
            'rating_labels_input' => ['nullable', 'string'],
        ]);

        $ratingLabels = $this->parseRatingLabels($data['type'], $data['rating_labels_input'] ?? null);

        $ulasanPermohonanQuestion->update([
            'question' => $data['question'],
            'type' => $data['type'],
            'category' => $data['category'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => $data['sort_order'],
            'note' => $data['note'] ?? null,
            'rating_labels' => $ratingLabels,
        ]);

        return redirect()->route($this->routePrefix($request) . '.ulasan-permohonan.index')
            ->with('success', 'Pertanyaan ulasan berhasil diperbarui.');
    }

    public function destroy(Request $request, UlasanPermohonanQuestion $ulasanPermohonanQuestion)
    {
        if (!$request->boolean('confirm')) {
            return redirect()->route($this->routePrefix($request) . '.ulasan-permohonan.index')
                ->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $ulasanPermohonanQuestion->delete();

        return redirect()->route($this->routePrefix($request) . '.ulasan-permohonan.index')
            ->with('success', 'Pertanyaan ulasan berhasil dihapus.');
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('admin.*') ? 'admin' : 'superadmin';
    }

    private function parseRatingLabels(string $type, ?string $raw): ?array
    {
        if ($type !== 'rating') {
            return null;
        }

        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(fn ($line) => trim((string) $line))
            ->values();

        if ($lines->filter(fn ($line) => $line !== '')->isEmpty()) {
            return null;
        }

        if ($lines->count() !== 4) {
            throw ValidationException::withMessages([
                'rating_labels_input' => 'Label opsi rating harus tepat 4 baris (untuk nilai 1-4).',
            ]);
        }

        return $lines->all();
    }
}
