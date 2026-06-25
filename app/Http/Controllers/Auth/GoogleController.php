<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

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
        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth InvalidStateException: ' . $e->getMessage());
            return redirect()->route('filament.admin.auth.login')
                ->with('google_error', 'Sesi login Google habis. Silakan coba lagi.');
        } catch (\Exception $e) {
            Log::error('Google OAuth gagal saat mendapatkan user: ' . $e->getMessage(), [
                'class' => get_class($e),
                'file'  => $e->getFile() . ':' . $e->getLine(),
            ]);
            return redirect()->route('filament.admin.auth.login')
                ->with('google_error', 'Login Google gagal. Silakan coba lagi.');
        }

        try {
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                $user->update([
                    'google_id'            => $googleUser->id,
                    'google_token'         => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]);
            } else {
                $user = User::create([
                    'name'                 => $googleUser->name,
                    'email'                => $googleUser->email,
                    'password'             => bcrypt(Str::random(24)),
                    'google_id'            => $googleUser->id,
                    'google_token'         => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'role'                 => 'petani',
                    'status'               => 'pending',
                ]);
            }

            if (($user->status ?? 'approved') === 'pending') {
                return redirect()->route('filament.admin.auth.login')
                    ->with('google_error', 'Akun Anda sedang menunggu persetujuan admin.');
            }

            if (($user->status ?? 'approved') === 'rejected') {
                return redirect()->route('filament.admin.auth.login')
                    ->with('google_error', 'Pendaftaran Anda tidak disetujui. Hubungi admin.');
            }

            Auth::login($user);

            return match ($user->role) {
                'petani'   => redirect()->route('petani.dashboard'),
                'pengepul' => redirect()->route('pengepul.dashboard'),
                'kub'      => redirect()->route('kub.dashboard'),
                'admin'    => redirect(Filament::getUrl()),
                default    => redirect()->route('filament.admin.auth.login'),
            };

        } catch (\Exception $e) {
            Log::error('Google OAuth error saat proses user: ' . $e->getMessage(), [
                'class'       => get_class($e),
                'file'        => $e->getFile() . ':' . $e->getLine(),
                'google_email'=> $googleUser->email ?? null,
            ]);
            return redirect()->route('filament.admin.auth.login')
                ->with('google_error', 'Terjadi kesalahan saat login. Silakan coba lagi.');
        }
    }
}
