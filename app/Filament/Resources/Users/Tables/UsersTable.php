<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'approved' => 'Disetujui',
                        'pending'  => 'Menunggu',
                        'rejected' => 'Ditolak',
                        default    => $state,
                    }),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin'    => 'danger',
                        'petani'   => 'success',
                        'pengepul' => 'info',
                        'kub'      => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'admin'    => 'Admin',
                        'petani'   => 'Petani',
                        'pengepul' => 'Pengepul',
                        'kub'      => 'KUB',
                        default    => $state,
                    }),

                TextColumn::make('petani.nama')
                    ->label('Link Petani')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('pengepul.nama_koperasi')
                    ->label('Link Pengepul')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Mendaftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'approved' => 'Disetujui',
                        'pending'  => 'Menunggu',
                        'rejected' => 'Ditolak',
                    ]),
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin'    => 'Admin',
                        'petani'   => 'Petani',
                        'pengepul' => 'Pengepul',
                        'kub'      => 'KUB',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->status !== 'approved')
                    ->action(function (User $record) {
                        $record->update(['status' => 'approved']);
                        Notification::make()->title('Akun disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Akun ditolak')->danger()->send();
                    }),

                EditAction::make()->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
