<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
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
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
