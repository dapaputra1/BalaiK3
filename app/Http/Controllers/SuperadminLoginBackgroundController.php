<?php

namespace App\Http\Controllers;

use App\Models\HomePopup;
use App\Models\LoginBackground;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperadminLoginBackgroundController extends Controller
{
    private const HOME_POPUP_DIRECTORY = 'images/home-popups';

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function index(): View
    {
        $backgrounds = LoginBackground::query()
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();

        $homePopups = HomePopup::query()
            ->ordered()
            ->get();

        return view('admin.superadmin_login_backgrounds', [
            'backgrounds' => $backgrounds,
            'homePopups' => $homePopups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'image' => $this->imageRules(['required']),
        ]);

        $relativePath = $this->storeImage($request);
        if (!$relativePath) {
            return back()->withErrors(['image' => 'Upload gambar tidak valid.'])->withInput();
        }

        LoginBackground::query()->create([
            'name' => $validated['name'],
            'image_path' => $relativePath,
            'is_active' => false,
        ]);

        return back()->with('success', 'Background login berhasil ditambahkan.');
    }

    public function update(Request $request, LoginBackground $loginBackground): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'image' => $this->imageRules(['nullable']),
        ]);

        $payload = [
            'name' => $validated['name'],
        ];

        if ($request->hasFile('image')) {
            $relativePath = $this->storeImage($request);
            if (!$relativePath) {
                return back()->withErrors(['image' => 'Upload gambar tidak valid.'])->withInput();
            }

            $oldPath = $loginBackground->image_path;
            $payload['image_path'] = $relativePath;
            $loginBackground->update($payload);
            $this->deleteManagedImage($oldPath);

            return back()->with('success', 'Background login berhasil diperbarui.');
        }

        $loginBackground->update($payload);

        return back()->with('success', 'Background login berhasil diperbarui.');
    }

    public function activate(LoginBackground $loginBackground): RedirectResponse
    {
        DB::transaction(function () use ($loginBackground): void {
            LoginBackground::query()->update(['is_active' => false]);
            $loginBackground->update(['is_active' => true]);
        });

        return back()->with('success', 'Background login aktif berhasil diperbarui.');
    }

    public function destroy(LoginBackground $loginBackground): RedirectResponse
    {
        if ($loginBackground->is_active) {
            return back()->withErrors(['delete' => 'Background aktif tidak bisa dihapus. Aktifkan background lain terlebih dahulu.']);
        }

        $this->deleteManagedImage($loginBackground->image_path);
        $loginBackground->delete();

        return back()->with('success', 'Background login berhasil dihapus.');
    }

    public function storeHomePopup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'popup_name' => ['required', 'string', 'max:100'],
            'popup_desktop' => ['required', ...$this->popupImageRules('desktop')],
            'popup_mobile' => ['required', ...$this->popupImageRules('mobile')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $desktopPath = $this->storeImageFromRequest($request, 'popup_desktop', self::HOME_POPUP_DIRECTORY);
        if (!$desktopPath) {
            return back()->withErrors(['popup_desktop' => 'Upload gambar popup desktop tidak valid.'])->withInput();
        }

        $mobilePath = $this->storeImageFromRequest($request, 'popup_mobile', self::HOME_POPUP_DIRECTORY);
        if (!$mobilePath) {
            return back()->withErrors(['popup_mobile' => 'Upload gambar popup mobile tidak valid.'])->withInput();
        }

        HomePopup::query()->create([
            'name' => $validated['popup_name'],
            'desktop_image_path' => $desktopPath,
            'mobile_image_path' => $mobilePath,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Popup home berhasil ditambahkan.');
    }

    public function updateHomePopup(Request $request, HomePopup $homePopup): RedirectResponse
    {
        $validated = $request->validate([
            'popup_name' => ['required', 'string', 'max:100'],
            'popup_desktop' => ['nullable', ...$this->popupImageRules('desktop')],
            'popup_mobile' => ['nullable', ...$this->popupImageRules('mobile')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $nextIsActive = (bool) ($validated['is_active'] ?? false);
        if (!$nextIsActive && $homePopup->is_active && $this->isLastActiveHomePopup($homePopup)) {
            return back()->withErrors(['popup_active' => 'Minimal harus ada 1 popup home yang aktif.'])->withInput();
        }

        $payload = [
            'name' => $validated['popup_name'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $nextIsActive,
        ];

        if ($request->hasFile('popup_desktop')) {
            $desktopPath = $this->storeImageFromRequest($request, 'popup_desktop', self::HOME_POPUP_DIRECTORY);
            if (!$desktopPath) {
                return back()->withErrors(['popup_desktop' => 'Upload gambar popup desktop tidak valid.'])->withInput();
            }

            $payload['desktop_image_path'] = $desktopPath;
            $this->deleteManagedImage($homePopup->desktop_image_path, self::HOME_POPUP_DIRECTORY);
        }

        if ($request->hasFile('popup_mobile')) {
            $mobilePath = $this->storeImageFromRequest($request, 'popup_mobile', self::HOME_POPUP_DIRECTORY);
            if (!$mobilePath) {
                return back()->withErrors(['popup_mobile' => 'Upload gambar popup mobile tidak valid.'])->withInput();
            }

            $payload['mobile_image_path'] = $mobilePath;
            $this->deleteManagedImage($homePopup->mobile_image_path, self::HOME_POPUP_DIRECTORY);
        }

        $homePopup->update($payload);

        return back()->with('success', 'Popup home berhasil diperbarui.');
    }

    public function toggleHomePopup(HomePopup $homePopup): RedirectResponse
    {
        if ($homePopup->is_active && $this->isLastActiveHomePopup($homePopup)) {
            return back()->withErrors(['popup_active' => 'Minimal harus ada 1 popup home yang aktif.'])->withInput();
        }

        $homePopup->update([
            'is_active' => !$homePopup->is_active,
        ]);

        return back()->with('success', 'Status popup home berhasil diperbarui.');
    }

    public function destroyHomePopup(HomePopup $homePopup): RedirectResponse
    {
        if ($homePopup->is_active && $this->isLastActiveHomePopup($homePopup)) {
            return back()->withErrors(['popup_active' => 'Popup aktif terakhir tidak bisa dihapus. Sisakan minimal 1 popup home aktif.']);
        }

        $this->deleteManagedImage($homePopup->desktop_image_path, self::HOME_POPUP_DIRECTORY);
        $this->deleteManagedImage($homePopup->mobile_image_path, self::HOME_POPUP_DIRECTORY);
        $homePopup->delete();

        return back()->with('success', 'Popup home berhasil dihapus.');
    }

    private function imageRules(array $presenceRules): array
    {
        return [
            ...$presenceRules,
            'file',
            'image',
            'mimetypes:image/jpeg,image/png,image/webp',
            'extensions:jpg,jpeg,png,webp',
            'dimensions:min_width=800,min_height=450,max_width=8000,max_height=8000',
            'max:5120',
        ];
    }

    private function popupImageRules(string $variant): array
    {
        $dimensionRule = $variant === 'mobile'
            ? 'dimensions:min_width=320,min_height=480,max_width=8000,max_height=8000'
            : 'dimensions:min_width=800,min_height=450,max_width=8000,max_height=8000';

        return [
            'file',
            'image',
            'mimetypes:image/jpeg,image/png,image/webp',
            'extensions:jpg,jpeg,png,webp',
            $dimensionRule,
            'max:5120',
        ];
    }

    private function storeImage(Request $request): ?string
    {
        return $this->storeImageFromRequest($request, 'image', 'images/login-backgrounds');
    }

    private function storeImageFromRequest(Request $request, string $field, string $directory): ?string
    {
        $file = $request->file($field);
        if (!$file || !$file->isValid()) {
            return null;
        }

        $imageInfo = @getimagesize($file->getRealPath());
        $mimeType = $imageInfo['mime'] ?? $file->getMimeType();
        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            return null;
        }

        File::ensureDirectoryExists(public_path($directory));

        $filename = Str::uuid()->toString() . '.' . self::ALLOWED_MIME_TYPES[$mimeType];
        $relativePath = trim($directory, '/') . '/' . $filename;

        $file->move(public_path($directory), $filename);

        return $relativePath;
    }

    private function deleteManagedImage(?string $imagePath, string $directory = 'images/login-backgrounds'): void
    {
        if (!$imagePath) {
            return;
        }

        $managedDirectory = realpath(public_path($directory));
        $absolutePath = realpath(public_path($imagePath));

        if (
            $managedDirectory &&
            $absolutePath &&
            Str::startsWith($absolutePath, $managedDirectory . DIRECTORY_SEPARATOR) &&
            File::exists($absolutePath)
        ) {
            File::delete($absolutePath);
        }
    }

    private function isLastActiveHomePopup(HomePopup $homePopup): bool
    {
        return HomePopup::query()
            ->where('is_active', true)
            ->whereKeyNot($homePopup->getKey())
            ->doesntExist();
    }
}
