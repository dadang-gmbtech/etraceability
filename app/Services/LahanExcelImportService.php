<?php

namespace App\Services;

use App\Models\Lahan;
use App\Models\Petani;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LahanExcelImportService
{
    /**
     * Import lahan dari file Excel.
     *
     * Mapping kolom (1-based):
     *  A=1  No
     *  B=2  Kode Petani (pemilik)
     *  C=3  Nama Pemilik Lahan
     *  D=4  Blok Lahan
     *  E=5  Kode Lahan
     *  F=6  Desa
     *  G=7  Koordinat GPS (lat, lng)
     *  H=8  Nama Penderes
     *  I=9  Kode Penderes
     *  J=10 Luas Lahan (ha)
     *  K=11 Pohon di Deres
     *  L=12 Kelapa Buah
     */
    public function import(string $storagePath): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        $fullPath = Storage::disk('local')->path($storagePath);
        if (! file_exists($fullPath)) {
            $result['errors'][] = 'File tidak ditemukan.';
            return $result;
        }

        try {
            $spreadsheet = IOFactory::load($fullPath);
        } catch (\Throwable $e) {
            $result['errors'][] = 'Gagal membaca file Excel: ' . $e->getMessage();
            return $result;
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false); // zero-based numeric index

        // Pass 1: agregasi data per kode_lahan (jumlahkan pohon dari baris duplikat)
        $aggregated = []; // kode_lahan => ['last' => row_data, 'pohon_sum' => int, 'buah_sum' => int]
        foreach ($rows as $i => $row) {
            if ($i === 0 || $this->isEmptyRow($row)) continue;

            $kodeLahan = trim((string) ($row[4] ?? ''));
            $kodeLahan = substr(preg_replace('/[^a-zA-Z0-9]/', '', $kodeLahan), 0, 9);
            if (empty($kodeLahan)) continue;

            $pohon = is_numeric($row[10] ?? null) ? (int) $row[10] : 0;
            $buah  = is_numeric($row[11] ?? null) ? (int) $row[11] : 0;

            if (! isset($aggregated[$kodeLahan])) {
                $aggregated[$kodeLahan] = ['row' => $row, 'rowNum' => $i + 1, 'pohon_sum' => 0, 'buah_sum' => 0];
            }
            // Data non-angka: selalu ambil baris terakhir yang punya nilai
            if (! empty(trim((string) ($row[2] ?? '')))) $aggregated[$kodeLahan]['row'][2] = $row[2]; // pemilik
            if (! empty(trim((string) ($row[3] ?? '')))) $aggregated[$kodeLahan]['row'][3] = $row[3]; // blok
            if (! empty(trim((string) ($row[5] ?? '')))) $aggregated[$kodeLahan]['row'][5] = $row[5]; // desa
            if (! empty(trim((string) ($row[6] ?? '')))) $aggregated[$kodeLahan]['row'][6] = $row[6]; // gps
            if (! empty(trim((string) ($row[7] ?? '')))) $aggregated[$kodeLahan]['row'][7] = $row[7]; // nama penderes
            if (! empty(trim((string) ($row[8] ?? '')))) $aggregated[$kodeLahan]['row'][8] = $row[8]; // kode penderes
            if (! empty($row[9]))                        $aggregated[$kodeLahan]['row'][9] = $row[9]; // luas

            // Pohon dan kelapa buah: dijumlahkan dari semua baris dengan kode sama
            $aggregated[$kodeLahan]['pohon_sum'] += $pohon;
            $aggregated[$kodeLahan]['buah_sum']  += $buah;
        }

        // Pass 2: upsert ke database
        foreach ($aggregated as $kodeLahan => $agg) {
            $row    = $agg['row'];
            $rowNum = $agg['rowNum'];

            try {
                $pemilik      = trim((string) ($row[2] ?? ''));
                $blokLahan    = trim((string) ($row[3] ?? ''));
                $desa         = trim((string) ($row[5] ?? ''));
                $gps          = trim((string) ($row[6] ?? ''));
                $namaPenderes = trim((string) ($row[7] ?? ''));
                $kodePenderes = trim((string) ($row[8] ?? ''));
                $luasLahan    = $row[9] ?? null;
                $pohonDiDeres = $agg['pohon_sum'];
                $kelapaBuah   = $agg['buah_sum'];

                // Cari atau buat Petani berdasarkan kode_penderes
                $petani = null;
                if (! empty($kodePenderes)) {
                    $petani = Petani::firstOrCreate(
                        ['kode_petani' => $kodePenderes],
                        ['nama' => $namaPenderes ?: $kodePenderes, 'aktif' => true]
                    );
                } elseif (! empty($namaPenderes)) {
                    $petani = Petani::where('nama', $namaPenderes)->first();
                }

                $koordinat = $this->parseGps($gps);
                $existing  = Lahan::where('kode_lahan', $kodeLahan)->first();

                if ($existing) {
                    $updateData = [
                        'petani_id'      => $petani?->id ?? $existing->petani_id,
                        'pemilik'        => $pemilik ?: $existing->pemilik,
                        'blok_lahan'     => $blokLahan ?: $existing->blok_lahan,
                        'desa'           => $desa ?: $existing->desa,
                        'luas_lahan'     => is_numeric($luasLahan) ? (float) $luasLahan : $existing->luas_lahan,
                        'pohon_di_deres' => $pohonDiDeres > 0 ? $pohonDiDeres : $existing->pohon_di_deres,
                        'kelapa_buah'    => $kelapaBuah > 0   ? $kelapaBuah   : $existing->kelapa_buah,
                    ];
                    if (empty($existing->koordinat) && $koordinat) {
                        $updateData['koordinat']      = $koordinat;
                        $updateData['jenis_geometri'] = 'titik';
                    }
                    $existing->update($updateData);
                    $result['updated']++;
                } else {
                    Lahan::create([
                        'petani_id'      => $petani?->id,
                        'kode_lahan'     => $kodeLahan,
                        'pemilik'        => $pemilik ?: null,
                        'blok_lahan'     => $blokLahan ?: null,
                        'desa'           => $desa ?: null,
                        'jenis_geometri' => $koordinat ? 'titik' : 'polygon',
                        'koordinat'      => $koordinat,
                        'luas_lahan'     => is_numeric($luasLahan) ? (float) $luasLahan : null,
                        'pohon_di_deres' => $pohonDiDeres,
                        'kelapa_buah'    => $kelapaBuah,
                    ]);
                    $result['created']++;
                }

            } catch (\Throwable $e) {
                $result['errors'][] = "Kode {$kodeLahan} (baris ~{$rowNum}): " . $e->getMessage();
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * Parse koordinat GPS dari string "lat, lng" ke GeoJSON Point.
     */
    private function parseGps(string $gps): ?array
    {
        if (empty($gps)) return null;

        // Format: "-7.3115008, 109.3130626" atau "-7.3115008,109.3130626"
        $parts = preg_split('/[\s,]+/', $gps, -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) < 2) return null;

        $lat = (float) $parts[0];
        $lng = (float) $parts[1];

        // Validasi range WGS84
        if (abs($lat) > 90 || abs($lng) > 180) return null;
        if ($lat === 0.0 && $lng === 0.0) return null;

        return [
            'type'        => 'Point',
            'coordinates' => [$lng, $lat], // GeoJSON: [longitude, latitude]
        ];
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') return false;
        }
        return true;
    }
}
