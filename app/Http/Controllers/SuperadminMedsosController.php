<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaPost;
use Illuminate\Http\Request;

class SuperadminMedsosController extends Controller
{
    public function index()
    {
        $posts = SocialMediaPost::query()
            ->with('uploader')
            ->latest('updated_at')
            ->latest('id')
            ->get();

        return view('admin.superadmin_medsos', [
            'posts' => $posts,
            'platforms' => SocialMediaPost::platformDefinitions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        SocialMediaPost::create([
            'user_id' => auth()->id(),
            'platform' => $data['platform'],
            'post_url' => $data['post_url'],
        ]);

        return redirect()
            ->route('superadmin.medsos.index')
            ->with('success', 'Konten media sosial berhasil ditambahkan.');
    }

    public function update(Request $request, SocialMediaPost $socialMediaPost)
    {
        $data = $this->validateData($request);

        $socialMediaPost->update([
            'platform' => $data['platform'],
            'post_url' => $data['post_url'],
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('superadmin.medsos.index')
            ->with('success', 'Konten media sosial berhasil diperbarui.');
    }

    public function destroy(SocialMediaPost $socialMediaPost)
    {
        $socialMediaPost->delete();

        return redirect()
            ->route('superadmin.medsos.index')
            ->with('success', 'Konten media sosial berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $request->merge([
            'post_url' => $this->normalizeUrlInput($request->input('post_url')),
        ]);

        return $request->validate([
            'platform' => ['required', 'string', 'in:' . implode(',', SocialMediaPost::platformKeys())],
            'post_url' => [
                'required',
                'url',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $platform = (string) $request->input('platform');

                    if ($platform === '') {
                        return;
                    }

                    if (!SocialMediaPost::supportsUrl($platform, (string) $value)) {
                        $fail('Link uploadan tidak sesuai dengan platform yang dipilih atau belum didukung. Gunakan link postingan publik yang valid.');
                    }
                },
            ],
        ], [], [
            'platform' => 'platform media sosial',
            'post_url' => 'link uploadan',
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
}
