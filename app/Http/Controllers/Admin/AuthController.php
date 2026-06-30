<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $limiter = app(RateLimiter::class);
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if ($limiter->tooManyAttempts($throttleKey, 5)) {
            $seconds = $limiter->availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $limiter->hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $limiter->clear($throttleKey);

        $request->session()->regenerate();

        if (! $request->user()?->is_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun tidak memiliki akses admin.',
            ]);
        }

        $intendedUrl = $request->session()->pull('url.intended');

        if (is_string($intendedUrl) && Str::startsWith($intendedUrl, url('/admin'))) {
            return redirect()->to($intendedUrl);
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
