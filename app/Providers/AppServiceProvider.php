<?php

namespace App\Providers;

use App\Models\Notifikasi;
use App\Services\KepalaBalaiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL)) {
            URL::forceScheme('https');
        }

        $passwordValidationTranslations = [
            'validation.required' => ':Attribute wajib diisi.',
            'validation.email' => ':Attribute harus berupa alamat email yang valid.',
            'validation.confirmed' => 'Konfirmasi :attribute tidak sesuai.',
            'validation.unique' => ':Attribute sudah terdaftar.',
            'validation.captcha' => 'Verifikasi captcha wajib diisi.',
            'validation.min.string' => ':Attribute minimal harus :min karakter.',
            'validation.password.mixed' => ':Attribute harus mengandung minimal satu huruf besar dan satu huruf kecil.',
            'validation.password.symbols' => ':Attribute harus mengandung minimal satu simbol.',
            'validation.password.numbers' => ':Attribute harus mengandung minimal satu angka.',
            'validation.password.uncompromised' => ':Attribute yang digunakan terdeteksi tidak aman. Silakan gunakan :attribute lain.',
            'validation.attributes.name' => 'nama pengguna',
            'validation.attributes.email' => 'email',
            'validation.attributes.password' => 'kata sandi',
            'validation.attributes.password_confirmation' => 'konfirmasi kata sandi',
            'validation.attributes.g-recaptcha-response' => 'verifikasi captcha',
            'validation.attributes.token' => 'token reset',
        ];

        Lang::addLines($passwordValidationTranslations, 'en');
        Lang::addLines($passwordValidationTranslations, 'id');

        Password::defaults(function () {
            return Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        View::composer('partials.navbar', function ($view) {
            $unreadNotifCount = 0;

            if (Auth::check()) {
                $unreadNotifCount = Notifikasi::query()
                    ->where('user_id', Auth::id())
                    ->whereNull('read_at')
                    ->count();
            }

            $view->with('unreadNotifCount', $unreadNotifCount);
        });

        View::composer('*', function ($view) {
            $kepalaBalai = app(KepalaBalaiService::class)->getProfile();

            $view->with('kepalaBalaiNama', $kepalaBalai['nama']);
            $view->with('kepalaBalaiNip', $kepalaBalai['nip']);
        });
    }
}
