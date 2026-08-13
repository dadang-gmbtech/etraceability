<?php

namespace App\Filament\Resources\Petanis\Tables;

use App\Filament\Resources\Petanis\PetaniResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PetanisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_petani')
                    ->label('Kode')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('nama')
                    ->label('Nama Petani')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                TextColumn::make('lahans_count')
                    ->label('Jumlah Lahan')
                    ->counts('lahans')
                    ->suffix(' lahan')
                    ->sortable(),
                TextColumn::make('total_pohon')
                    ->label('Total Pohon')
                    ->getStateUsing(fn ($record) => $record->lahans()->sum('pohon_di_deres'))
                    ->suffix(' pohon'),
                TextColumn::make('desa')
                    ->label('Desa')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('kecamatan')
                    ->label('Kecamatan')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('punya_akun')
                    ->label('Punya Akun')
                    ->getStateUsing(fn ($record) => User::where('petani_id', $record->id)->exists())
                    ->boolean()
                    ->trueIcon('heroicon-o-user-circle')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('rekap_setoran')
                    ->label('Rekap Setoran')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn ($record) => PetaniResource::getUrl('rekap', ['record' => $record])),
                EditAction::make(),

                Action::make('buat_akun')
                    ->label('Buat Akun')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn ($record) => ! User::where('petani_id', $record->id)->exists())
                    ->modalHeading(fn ($record) => 'Buat Akun untuk ' . $record->nama)
                    ->modalDescription('Akun ini digunakan petani untuk login ke portal dan melihat rekap setoran.')
                    ->modalWidth('md')
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Akun')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->rules([Rule::unique('users', 'email')])
                            ->required(),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->minLength(6)
                            ->required(),
                    ])
                    ->fillForm(fn ($record) => ['name' => $record->nama])
                    ->action(function ($record, array $data): void {
                        User::create([
                            'name'      => $data['name'],
                            'email'     => $data['email'],
                            'password'  => bcrypt($data['password']),
                            'role'      => 'petani',
                            'status'    => 'approved',
                            'petani_id' => $record->id,
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Akun berhasil dibuat')
                            ->body("Petani {$record->nama} kini dapat login ke portal dengan email {$data['email']}.")
                            ->send();
                    }),

                Action::make('lihat_akun')
                    ->label('Lihat Akun')
                    ->icon('heroicon-o-user-circle')
                    ->color('gray')
                    ->visible(fn ($record) => User::where('petani_id', $record->id)->exists())
                    ->url(fn ($record) => route('filament.admin.resources.users.index') . '?tableSearch=' . urlencode($record->nama)),

                Action::make('cetak_qr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn ($record) => route('petani.qrcode', $record->kode_petani))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
