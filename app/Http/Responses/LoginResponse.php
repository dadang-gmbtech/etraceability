<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role ?? 'admin') {
            'petani'   => redirect()->route('petani.dashboard'),
            'pengepul' => redirect()->route('pengepul.dashboard'),
            'kub'      => redirect()->route('kub.dashboard'),
            default    => redirect()->intended(Filament::getUrl()),
        };
    }
}
