<?php

namespace App\Filament\Resources\BatasWilayahs\Pages;

use App\Filament\Resources\BatasWilayahs\BatasWilayahResource;
use App\Models\BatasWilayah;
use App\Services\BatasWilayahImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBatasWilayahs extends ListRecords
{
    protected static string $resource = BatasWilayahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Import Kecamatan ──────────────────────────────────────────
            Action::make('importKecamatan')
                ->label('Import Kecamatan')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('Import Batas Kecamatan dari Shapefile')
                ->modalDescription('Upload file ZIP yang berisi shapefile batas kecamatan (.shp, .shx, .dbf, .prj).')
                ->modalWidth('lg')
                ->form([
                    FileUpload::make('shp_zip')
                        ->label('File ZIP Shapefile')
                        ->helperText('Kompres file .shp + .shx + .dbf + .prj menjadi satu file .zip')
                        ->required()
                        ->disk('local')
                        ->directory('batas-imports')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/zip',
                            'application/x-zip-compressed',
                            'application/octet-stream',
                        ])
                        ->maxSize(50 * 1024),
                ])
                ->action(function (array $data): void {
                    $service = new BatasWilayahImportService();
                    $result  = $service->importFromZip($data['shp_zip'], 'kecamatan');

                    if ($result['created'] > 0) {
                        $msg = "Berhasil mengimpor {$result['created']} batas kecamatan.";
                        if ($result['skipped'] > 0) {
                            $msg .= " {$result['skipped']} fitur dilewati.";
                        }
                        Notification::make()
                            ->title('Import Kecamatan Berhasil')
                            ->body($msg)
                            ->success()
                            ->send();
                    } else {
                        $errorDetail = implode(' | ', array_slice($result['errors'], 0, 2));
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Tidak ada data yang berhasil diimport. ' . $errorDetail)
                            ->danger()
                            ->send();
                    }

                    foreach (array_slice($result['errors'], 0, 5) as $err) {
                        Notification::make()
                            ->title('Peringatan')
                            ->body($err)
                            ->warning()
                            ->send();
                    }
                }),

            // ── Hapus Kecamatan ───────────────────────────────────────────
            Action::make('hapusSemuaKecamatan')
                ->label('Hapus Semua Kecamatan')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Semua Batas Kecamatan?')
                ->modalDescription('Tindakan ini akan menghapus semua data batas kecamatan. Tidak dapat dibatalkan.')
                ->action(function (): void {
                    $count = BatasWilayah::where('jenis', 'kecamatan')->count();
                    BatasWilayah::where('jenis', 'kecamatan')->delete();
                    Notification::make()->title("$count batas kecamatan dihapus.")->success()->send();
                }),

            // ── Hapus Desa ────────────────────────────────────────────────
            Action::make('hapusSemuaDesa')
                ->label('Hapus Semua Desa')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Hapus Semua Batas Desa?')
                ->modalDescription('Tindakan ini akan menghapus semua data batas desa. Tidak dapat dibatalkan.')
                ->action(function (): void {
                    $count = BatasWilayah::where('jenis', 'desa')->count();
                    BatasWilayah::where('jenis', 'desa')->delete();
                    Notification::make()->title("$count batas desa dihapus.")->success()->send();
                }),

            // ── Import Desa ───────────────────────────────────────────────
            Action::make('importDesa')
                ->label('Import Desa')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Batas Desa dari Shapefile')
                ->modalDescription('Upload file ZIP yang berisi shapefile batas desa/kelurahan (.shp, .shx, .dbf, .prj).')
                ->modalWidth('lg')
                ->form([
                    FileUpload::make('shp_zip')
                        ->label('File ZIP Shapefile')
                        ->helperText('Kompres file .shp + .shx + .dbf + .prj menjadi satu file .zip')
                        ->required()
                        ->disk('local')
                        ->directory('batas-imports')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/zip',
                            'application/x-zip-compressed',
                            'application/octet-stream',
                        ])
                        ->maxSize(50 * 1024),
                ])
                ->action(function (array $data): void {
                    $service = new BatasWilayahImportService();
                    $result  = $service->importFromZip($data['shp_zip'], 'desa');

                    if ($result['created'] > 0) {
                        $msg = "Berhasil mengimpor {$result['created']} batas desa.";
                        if ($result['skipped'] > 0) {
                            $msg .= " {$result['skipped']} fitur dilewati.";
                        }
                        Notification::make()
                            ->title('Import Desa Berhasil')
                            ->body($msg)
                            ->success()
                            ->send();
                    } else {
                        $errorDetail = implode(' | ', array_slice($result['errors'], 0, 2));
                        Notification::make()
                            ->title('Import Gagal')
                            ->body('Tidak ada data yang berhasil diimport. ' . $errorDetail)
                            ->danger()
                            ->send();
                    }

                    foreach (array_slice($result['errors'], 0, 5) as $err) {
                        Notification::make()
                            ->title('Peringatan')
                            ->body($err)
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }

}
