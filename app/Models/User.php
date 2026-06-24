<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'google_id', 'google_token', 'google_refresh_token', 'api_token', 'role', 'status', 'petani_id', 'pengepul_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    public function petani(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Petani::class);
    }

    public function pengepul(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Pengepul::class);
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isPetani(): bool   { return $this->role === 'petani'; }
    public function isPengepul(): bool { return $this->role === 'pengepul'; }
    public function isKub(): bool      { return $this->role === 'kub'; }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->api_token)) {
                $user->api_token = Str::random(48);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
