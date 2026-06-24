<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Petani;
use App\Models\Pengepul;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Kosongkan jika tidak ingin mengubah password'),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin'    => 'Admin',
                        'petani'   => 'Petani',
                        'pengepul' => 'Pengepul',
                        'kub'      => 'KUB',
                    ])
                    ->default('admin')
                    ->required()
                    ->live(),

                Select::make('petani_id')
                    ->label('Link ke Data Petani')
                    ->options(Petani::orderBy('nama')->pluck('nama', 'id'))
                    ->searchable()
                    ->nullable()
                    ->visible(fn ($get) => $get('role') === 'petani')
                    ->helperText('Pilih petani yang terhubung dengan akun ini'),

                Select::make('pengepul_id')
                    ->label('Link ke Data Pengepul')
                    ->options(Pengepul::orderBy('nama_koperasi')->pluck('nama_koperasi', 'id'))
                    ->searchable()
                    ->nullable()
                    ->visible(fn ($get) => $get('role') === 'pengepul')
                    ->helperText('Pilih pengepul yang terhubung dengan akun ini'),
            ]);
    }
}
