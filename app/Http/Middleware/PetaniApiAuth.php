<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PetaniApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->header('X-Api-Token');

        if (! $token) {
            return response()->json(['message' => 'Token tidak ditemukan. Sertakan Bearer token.'], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (! $user || ! $user->isPetani() || $user->status !== 'approved') {
            return response()->json(['message' => 'Token tidak valid atau akun tidak aktif.'], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
