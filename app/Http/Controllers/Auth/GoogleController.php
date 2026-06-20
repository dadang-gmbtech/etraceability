<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah user sudah ada berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // Update google auth token jika sudah ada
                $user->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            }

            Auth::login($user);

            return redirect()->intended(route('peta.sebaran', absolute: false));

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'Terjadi kesalahan saat login dengan Google: ' . $e->getMessage()]);
        }
    }
}
