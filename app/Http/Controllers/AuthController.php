<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password as PasswordRule;


class AuthController extends Controller
{
    // Show Register Form
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Handle Register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'g-recaptcha-response' => 'required|captcha'
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_active' => 1
        ]);

        return redirect('/login')->with('success', 'Register berhasil, silakan login.');
    }

    // Show Login Form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle Login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required|captcha'
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => 1,
        ];

        if (!Auth::attempt($credentials, false)) {
            return back()
                ->withErrors(['email' => 'Kredensial tidak valid atau akun nonaktif.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $role = strtolower(trim((string) $request->user()?->role));

        return redirect()->intended($this->redirectPathByRole($role));

    }

    

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectPathByRole(string $role): string
    {
        if (Route::has($role . '.dashboard')) {
            return route($role . '.dashboard');
        }

        return match ($role) {
            'mp', 'mt' => route('superadmin.disposisi.index'),
            default => url('/'),
        };
    }

}
