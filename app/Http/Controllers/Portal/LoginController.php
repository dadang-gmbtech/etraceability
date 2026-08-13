<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->isPetani()) {
            return redirect()->route('portal.rekap');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isPetani()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun ini bukan akun petani.'])->onlyInput('email');
            }

            if ($user->status !== 'approved') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda belum disetujui oleh admin.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('portal.rekap'));
        }

        return back()
            ->withErrors(['email' => 'Email atau password tidak sesuai.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }
}
