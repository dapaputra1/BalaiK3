<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\BeritaView;
use GdImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SuperadminBeritaController extends Controller
{
    private const NEWS_IMAGE_WIDTH = 1600;
    private const NEWS_IMAGE_HEIGHT = 900;
    private const NEWS_IMAGE_EXTENSION = 'jpg';

    public function index()
    {
        $beritas = Berita::query()
            ->with(['uploader', 'viewCounter'])
            ->latest()
            ->get();

        return view('admin.superadmin_berita', [
            'beritas' => $beritas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBerita($request);

        $uploadDate = $this->parseUploadDate($data['upload_date']);
        $imagePath = $this->tryStoreImage($request);
        if ($imagePath instanceof \Illuminate\Http\RedirectResponse) {
            return $imagePath;
        }

        $berita = new Berita([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'slug' => $this->generateUniqueSlug($data['title']),
            'content' => $data['content'],
            'image_path' => $imagePath ?: 'images/header2.jpg',
        ]);
        $berita->created_at = $uploadDate;
        $berita->updated_at = $uploadDate;
        $berita->save();

        BeritaView::query()->firstOrCreate([
            'slug' => $berita->slug,
        ], [
            'views_count' => 0,
        ]);

        return redirect()
            ->route('superadmin.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function update(Request $request, Berita $beritum)
    {
        $data = $this->validateBerita($request);

        $oldSlug = $beritum->slug;
        $newImagePath = $this->tryStoreImage($request);
        if ($newImagePath instanceof \Illuminate\Http\RedirectResponse) {
            return $newImagePath;
        }
        $uploadDate = $this->parseUploadDate($data['upload_date']);

        $beritum->fill([
            'title' => $data['title'],
            'slug' => $this->generateUniqueSlug($data['title'], $beritum->id),
            'content' => $data['content'],
        ]);
        $beritum->created_at = $uploadDate;

        if ($newImagePath) {
            $this->deleteImage($beritum->image_path);
            $beritum->image_path = $newImagePath;
        }

        $beritum->save();

        if ($oldSlug !== $beritum->slug) {
            BeritaView::query()
                ->where('slug', $oldSlug)
                ->update(['slug' => $beritum->slug]);
        }

        return redirect()
            ->route('superadmin.berita.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita $beritum)
    {
        $slug = $beritum->slug;

        $this->deleteImage($beritum->image_path);
        $beritum->delete();

        BeritaView::query()->where('slug', $slug)->delete();

        return redirect()
            ->route('superadmin.berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        if ($baseSlug === '') {
            $baseSlug = 'berita';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Berita::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeImage(?UploadedFile $image): ?string
    {
        if (!$image) {
            return null;
        }

        $directory = public_path('images/berita');
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (!File::isWritable($directory)) {
            throw new \RuntimeException('Folder gambar berita tidak dapat ditulis server.');
        }

        $extension = self::NEWS_IMAGE_EXTENSION;
        $filename = Str::uuid()->toString() . '.' . $extension;
        $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;

        $this->resizeAndStoreImage($image, $targetPath, $extension);

        return 'images/berita/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path || !Str::startsWith($path, 'images/berita/')) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function parseUploadDate(string $value): Carbon
    {
        return Carbon::parse($value)->seconds(0);
    }

    private function validateBerita(Request $request): array
    {
        return $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string'],
                'upload_date' => ['required', 'date'],
                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:20480',
                ],
            ],
            [
                'image.max' => 'Ukuran file gambar maksimal 20 MB.',
            ]
        );
    }

    private function resizeAndStoreImage(UploadedFile $image, string $targetPath, string $extension): void
    {
        foreach (['imagecreatefromstring', 'imagecreatetruecolor', 'imagecopyresampled', 'imagejpeg'] as $function) {
            if (!function_exists($function)) {
                throw new \RuntimeException('Ekstensi PHP GD belum aktif di server.');
            }
        }

        $imageBinary = file_get_contents($image->getRealPath());
        $source = $imageBinary !== false ? imagecreatefromstring($imageBinary) : false;
        if (!$source) {
            throw new \RuntimeException('Format gambar tidak didukung oleh server.');
        }

        $canvas = imagecreatetruecolor(self::NEWS_IMAGE_WIDTH, self::NEWS_IMAGE_HEIGHT);
        if (!$canvas) {
            imagedestroy($source);
            throw new \RuntimeException('Gagal menyiapkan kanvas gambar.');
        }

        $background = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $background);

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $scale = min(
            self::NEWS_IMAGE_WIDTH / max($sourceWidth, 1),
            self::NEWS_IMAGE_HEIGHT / max($sourceHeight, 1)
        );

        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        $targetX = (int) floor((self::NEWS_IMAGE_WIDTH - $targetWidth) / 2);
        $targetY = (int) floor((self::NEWS_IMAGE_HEIGHT - $targetHeight) / 2);

        imagecopyresampled(
            $canvas,
            $source,
            $targetX,
            $targetY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        $this->saveImageResource($canvas, $targetPath, $extension);

        imagedestroy($canvas);
        imagedestroy($source);
    }

    private function saveImageResource(GdImage $image, string $path, string $extension): void
    {
        $saved = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $path, 85),
            'png' => imagepng($image, $path, 6),
            'webp' => function_exists('imagewebp') ? imagewebp($image, $path, 85) : false,
            default => false,
        };

        if (!$saved) {
            throw new \RuntimeException('Gagal menyimpan gambar berita.');
        }
    }

    private function tryStoreImage(Request $request): string|\Illuminate\Http\RedirectResponse|null
    {
        try {
            return $this->storeImage($request->file('image'));
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['image' => $exception->getMessage()])
                ->withInput();
        }
    }
}
