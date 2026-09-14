<?php

namespace App\Http\Controllers;

use App\Services\KepalaBalaiService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PetugasController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';
    private const MAX_SIGNATURE_BYTES = 1048576; // 1 MB
    private const MAX_SIGNATURE_WIDTH = 2000;
    private const MAX_SIGNATURE_HEIGHT = 2000;
    private const ALLOWED_ROLES = ['admin', 'ma', 'superadmin', 'mp', 'mt', 'penyelia', 'pcu', 'analis', 'qc'];

    public function index()
    {
        $petugas = User::whereIn('role', self::ALLOWED_ROLES)
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                $user->signature_url = $user->signature_path ? route('petugas.signature', $user) : '';
                return $user;
            });

        return view('admin.superadmin_managepetugas', [
            'petugas' => $petugas,
            'allowedRoles' => self::ALLOWED_ROLES,
            'kepalaBalai' => app(KepalaBalaiService::class)->getProfile(),
        ]);
    }

    public function updateKepalaBalai(Request $request, KepalaBalaiService $kepalaBalaiService)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nip' => ['required', 'string', 'max:50'],
        ], [
            'nama.required' => 'Nama kepala balai wajib diisi.',
            'nip.required' => 'NIP kepala balai wajib diisi.',
        ]);

        $kepalaBalaiService->updateProfile($data['nama'], $data['nip']);

        return redirect()
            ->route('superadmin.petugas.index')
            ->with('success', 'Data kepala balai berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'golongan' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'signature_data' => ['nullable', 'string'],
        ]);

        $signaturePath = $this->storeSignature($data['signature_data'] ?? null);
        if (($data['signature_data'] ?? null) && !$signaturePath) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['signature_data' => 'Tanda tangan petugas tidak valid. Silakan gambar ulang. Ukuran maksimal 1 MB.']);
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'nip' => trim((string) ($data['nip'] ?? '')) ?: null,
            'jabatan' => trim((string) ($data['jabatan'] ?? '')) ?: null,
            'golongan' => trim((string) ($data['golongan'] ?? '')) ?: null,
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'is_active' => 1,
            'signature_path' => $signaturePath,
        ]);

        return redirect()->route('superadmin.petugas.index')->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function update(Request $request, User $petuga)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($petuga->id)],
            'nip' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'golongan' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
            'signature_data' => ['nullable', 'string'],
            'signature_remove' => ['nullable', 'boolean'],
        ]);

        $petuga->name = $data['name'];
        $petuga->email = $data['email'];
        $petuga->nip = trim((string) ($data['nip'] ?? '')) ?: null;
        $petuga->jabatan = trim((string) ($data['jabatan'] ?? '')) ?: null;
        $petuga->golongan = trim((string) ($data['golongan'] ?? '')) ?: null;
        $petuga->role = $data['role'];
        if (!empty($data['password'])) {
            $petuga->password = Hash::make($data['password']);
        }
        if (!empty($data['signature_data'])) {
            $newPath = $this->storeSignature($data['signature_data']);
            if (!$newPath) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['signature_data' => 'Tanda tangan petugas tidak valid. Silakan gambar ulang. Ukuran maksimal 1 MB.']);
            }

            $this->deleteSignature($petuga->signature_path);
            $petuga->signature_path = $newPath;
        } elseif (!empty($data['signature_remove'])) {
            $this->deleteSignature($petuga->signature_path);
            $petuga->signature_path = null;
        }
        $petuga->save();

        return redirect()->route('superadmin.petugas.index')->with('success', 'Petugas berhasil diperbarui.');
    }

    public function editProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user && in_array((string) $user->role, self::ALLOWED_ROLES, true), 403);

        $user->signature_url = $user->signature_path ? route('petugas.signature.me') : '';

        return view('admin.petugas_profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user && in_array((string) $user->role, self::ALLOWED_ROLES, true), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'golongan' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
            'signature_data' => ['nullable', 'string'],
            'signature_remove' => ['nullable', 'boolean'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->nip = trim((string) ($data['nip'] ?? '')) ?: null;
        $user->jabatan = trim((string) ($data['jabatan'] ?? '')) ?: null;
        $user->golongan = trim((string) ($data['golongan'] ?? '')) ?: null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (!empty($data['signature_data'])) {
            $newPath = $this->storeSignature($data['signature_data']);
            if (!$newPath) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['signature_data' => 'Tanda tangan tidak valid. Silakan gambar ulang. Ukuran maksimal 1 MB.']);
            }

            $this->deleteSignature($user->signature_path);
            $user->signature_path = $newPath;
        } elseif (!empty($data['signature_remove'])) {
            $this->deleteSignature($user->signature_path);
            $user->signature_path = null;
        }

        $user->save();

        return redirect()->route('petugas.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroy(Request $request, User $petuga)
    {
        if (!$request->boolean('confirm')) {
            return redirect()->route('superadmin.petugas.index')->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $this->deleteSignature($petuga->signature_path);
        $petuga->delete();

        return redirect()->route('superadmin.petugas.index')->with('success', 'Petugas berhasil dihapus.');
    }

    private function storeSignature(?string $data): ?string
    {
        if (!$data) {
            return null;
        }

        $data = trim($data);
        if (!preg_match('#^data:image/(png|jpeg);base64,(.+)$#s', $data, $matches)) {
            return null;
        }

        $base64 = preg_replace('/\s+/', '', $matches[2] ?? '');
        if ($base64 === '') {
            return null;
        }

        $maxBase64Length = (int) ceil(self::MAX_SIGNATURE_BYTES * 4 / 3) + 16;
        if (strlen($base64) > $maxBase64Length) {
            return null;
        }

        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '' || strlen($binary) > self::MAX_SIGNATURE_BYTES) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($binary);
        if (!is_array($imageInfo)) {
            return null;
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        $mime = strtolower((string) ($imageInfo['mime'] ?? ''));

        if ($width < 1 || $height < 1 || $width > self::MAX_SIGNATURE_WIDTH || $height > self::MAX_SIGNATURE_HEIGHT) {
            return null;
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            default => null,
        };

        if ($extension === null) {
            return null;
        }

        $path = 'signatures/' . Str::uuid()->toString() . '.' . $extension;
        Storage::disk(self::PRIVATE_DISK)->put($path, $binary);

        return $path;
    }

    public function signature(User $petuga)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $isOwner = (int) $user->id === (int) $petuga->id;
        $isAdmin = in_array((string) $user->role, ['admin', 'superadmin'], true);
        if (!$isOwner && !$isAdmin) {
            abort(403);
        }

        return $this->signatureResponse($petuga->signature_path);
    }

    public function mySignature()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        return $this->signatureResponse($user->signature_path);
    }

    private function signatureResponse(?string $path)
    {
        $disk = $this->resolveDisk($path);
        if (!$path || $disk === null) {
            abort(404);
        }

        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/png';
        return Storage::disk($disk)->response($path, basename($path), [
            'Content-Type' => $mime,
        ]);
    }

    private function resolveDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            return self::PRIVATE_DISK;
        }
        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            return self::LEGACY_DISK;
        }
        return null;
    }

    private function deleteSignature(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($path);
        }
        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            Storage::disk(self::LEGACY_DISK)->delete($path);
        }
    }
}
