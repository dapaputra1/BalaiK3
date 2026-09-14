<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\BeritaView;
use Illuminate\Support\Facades\Cache;

class BeritaController extends Controller
{
    public function index()
    {
        $articles = Berita::query()
            ->with(['uploader', 'viewCounter'])
            ->latest()
            ->get();

        return view('berita', [
            'featured' => $articles->first(),
            'newsCards' => $articles->slice(1)->values(),
            'articlesCount' => $articles->count(),
        ]);
    }

    public function show(string $slug)
    {
        $article = Berita::query()
            ->with(['uploader', 'viewCounter'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->registerView($article);
        $article->load(['uploader', 'viewCounter']);

        $relatedArticles = Berita::query()
            ->with(['uploader', 'viewCounter'])
            ->where('id', '!=', $article->id)
            ->latest()
            ->take(3)
            ->get();

        return view('berita_detail', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    private function registerView(Berita $article): void
    {
        $viewKey = 'berita_viewed:' . $article->id . ':' . session()->getId();

        if (!Cache::add($viewKey, true, now()->addMinutes(30))) {
            return;
        }

        BeritaView::query()->firstOrCreate(['slug' => $article->slug]);
        BeritaView::query()
            ->where('slug', $article->slug)
            ->increment('views_count');
    }
}

