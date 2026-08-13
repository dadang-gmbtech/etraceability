<?php

namespace App\Filament\Resources\Lahans\Pages;

use App\Exports\LahanTemplateExport;
use App\Filament\Resources\Lahans\LahanResource;
use App\Models\Petani;
use App\Services\LahanExcelImportService;
use App\Services\ShpImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListLahans extends ListRecords
{
    protected static string $resource = LahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Download Template Excel ───────────────────────────────────
            Action::make('downloadTemplate')
                ->label('Template Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return Excel::download(
                        new LahanTemplateExport(),
                        'template_import_lahan.xlsx'
                    );
                }),

            // ── Import Excel ──────────────────────────────────────────────
            Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-table-cells')
                ->color('warning')
                ->modalHeading('Import Data Lahan dari Excel')
                ->modalDescription('Upload file Excel (.xlsx) sesuai format template. Download template terlebih dahulu jika belum memilikinya.')
                ->modalWidth('lg')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('File Excel (.xlsx / .xls)')
                        ->helperText('Gunakan template yang sudah disediakan')
                        ->required()
                        ->disk('local')
                        ->directory('excel-imports')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/octet-stream',
                        ])
                        ->maxSize(20 * 1024), // 20 MB
                ])
                ->action(function (array $data): void {
                    $service = new LahanExcelImportService();
                    $result  = $service->import($data['excel_file']);

                    $created = $result['created'] ?? 0;
                    $updated = $result['updated'] ?? 0;
                    $skipped = $result['skipped'] ?? 0;

                    if ($created > 0 || $updated > 0) {
                        $parts = [];
                        if ($created > 0) $parts[] = "{$created} data baru ditambahkan";
                        if ($updated > 0) $parts[] = "{$updated} data diperbarui";
                        if ($skipped > 0) $parts[] = "{$skipped} baris dilewati";
                        Notification::make()
                            ->title('Import Berhasil')
                            ->body(implode(', ', $parts) . '.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Tidak Ada Perubahan')
                            ->body('Semua baris dilewati atau file kosong.')
                            ->warning()
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

            // ── Import Shapefile ──────────────────────────────────────────
            Action::make('importShp')
                ->label('Import Shapefile')
                ->icon('heroicon-o-map')
                ->color('success')
                ->modalHeading('Import Data Lahan dari Shapefile')
                ->modalDescription('Upload file ZIP yang berisi .shp, .shx, .dbf, dan .prj. Setiap fitur dalam shapefile menjadi satu data lahan.')
                ->modalWidth('xl')
                ->form([
                    FileUpload::make('shp_zip')
                        ->label('File Shapefile (.zip)')
                        ->helperText('Kompres file .shp + .shx + .dbf + .prj menjadi satu file .zip')
                        ->required()
                        ->disk('local')
                        ->directory('shp-imports')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/zip',
                            'application/x-zip-compressed',
                            'application/octet-stream',
                        ])
                        ->maxSize(50 * 1024),

                    Select::make('petani_id')
                        ->label('Penderes (default)')
                        ->helperText('Opsional — semua lahan yang diimport ditetapkan ke penderes ini')
                        ->options(
                            Petani::orderBy('nama')->get()->mapWithKeys(fn ($p) => [
                                $p->id => $p->nama . ' (' . $p->kode_petani . ')',
                            ])
                        )
                        ->searchable()
                        ->nullable(),

                    TextInput::make('blok_lahan')
                        ->label('Blok Lahan (default)')
                        ->helperText('Dipakai jika shapefile tidak memiliki kolom blok_lahan')
                        ->maxLength(50)
                        ->nullable(),

                    TextInput::make('desa')
                        ->label('Desa (default)')
                        ->helperText('Dipakai jika shapefile tidak memiliki kolom desa')
                        ->maxLength(100)
                        ->nullable(),
                ])
                ->action(function (array $data): void {
                    $service = new ShpImportService();
                    $result  = $service->importFromZip($data['shp_zip'], [
                        'petani_id'  => $data['petani_id']  ?? null,
                        'blok_lahan' => $data['blok_lahan'] ?? null,
                        'desa'       => $data['desa']        ?? null,
                    ]);

                    if ($result['created'] > 0) {
                        $msg = "Berhasil mengimpor {$result['created']} lahan dari shapefile.";
                        if ($result['skipped'] > 0) {
                            $msg .= " {$result['skipped']} fitur dilewati.";
                        }
                        Notification::make()
                            ->title('Import Berhasil')
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

            CreateAction::make(),
        ];
    }
}
