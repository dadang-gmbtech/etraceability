<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements Responsable
{
    public function toResponse($request): Response
    {
        $user = auth()->user();

        $url = match ($user->role ?? 'admin') {
            'petani'   => route('petani.dashboard'),
            'pengepul' => route('pengepul.dashboard'),
            'kub'      => route('kub.dashboard'),
            default    => session()->pull('url.intended', Filament::getUrl()),
        };

        return new RedirectResponse($url);
    }
}
