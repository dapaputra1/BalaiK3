<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Throwable;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            Log::error('Failed to send password reset email.', [
                'email' => $request->email,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Layanan email sedang bermasalah. Silakan coba lagi beberapa saat lagi.',
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            Log::warning('Password reset link was not sent.', [
                'email' => $request->email,
                'status' => $status,
            ]);
        }

        return back()->with([
            'success' => 'Jika email terdaftar, link reset password telah dikirim.',
        ]);
    }

    public function resetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }



    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()]
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $payload = [
                    'password' => Hash::make($request->password),
                ];

                if (Schema::hasColumn($user->getTable(), 'remember_token')) {
                    $payload['remember_token'] = Str::random(60);
                }

                $user->forceFill($payload)->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil direset!')
            : back()->withErrors(['email' => 'Reset gagal, token kadaluwarsa atau tidak cocok.']);
    }
}
