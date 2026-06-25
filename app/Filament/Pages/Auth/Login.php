<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::auth/pages/login.form.password.label'))
            ->password()
            ->revealable()
            ->autocomplete('current-password')
            ->required();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data        = $this->form->getState();
        $authGuard   = Filament::auth();
        $credentials = $this->getCredentialsFromFormData($data);
        $remember    = $data['remember'] ?? false;

        if (! $authGuard->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
            ]);
        }

        $user = auth()->user();

        if (($user->status ?? 'approved') === 'pending') {
            $authGuard->logout();
            throw ValidationException::withMessages([
                'data.email' => 'Akun Anda sedang menunggu persetujuan admin.',
            ]);
        }

        if (($user->status ?? 'approved') === 'rejected') {
            $authGuard->logout();
            throw ValidationException::withMessages([
                'data.email' => 'Pendaftaran Anda tidak disetujui. Hubungi admin.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
