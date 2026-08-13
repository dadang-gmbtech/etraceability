<?php

namespace App\Services;

use App\Models\Lahan;
use App\Models\Petani;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ShpImportService
{
    /**
     * Import lahan records dari ZIP shapefile.
     *
     * @param  string  $zipStoragePath  Path relatif di storage (dari Filament FileUpload)
     * @param  array   $defaults        Default values: petani_id, blok_lahan, desa, dll
     * @return array   ['created' => int, 'skipped' => int, 'errors' => string[]]
     */
    public function importFromZip(string $zipStoragePath, array $defaults = []): array
    {
        $result = ['created' => 0, 'skipped' => 0, 'errors' => []];

        $zipFullPath = Storage::disk('local')->path($zipStoragePath);
        if (! file_exists($zipFullPath)) {
            $result['errors'][] = 'File ZIP tidak ditemukan.';
            return $result;
        }

        // Ekstrak ke direktori temp
        $tempDir = sys_get_temp_dir() . '/shp_import_' . uniqid();
        mkdir($tempDir, 0755, true);

        try {
            $zip = new ZipArchive();
            if ($zip->open($zipFullPath) !== true) {
                $result['errors'][] = 'Gagal membuka file ZIP.';
                return $result;
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // Cari file .shp di dalam ekstrak (termasuk dalam sub-folder)
            $shpFiles = glob($tempDir . '/**/*.shp') ?: [];
            $shpFiles = array_merge($shpFiles, glob($tempDir . '/*.shp') ?: []);

            if (empty($shpFiles)) {
                $result['errors'][] = 'Tidak ditemukan file .shp di dalam ZIP. Pastikan ZIP berisi file .shp, .shx, .dbf, dan .prj.';
                return $result;
            }

            foreach ($shpFiles as $shpFile) {
                $this->processShpFile($shpFile, $defaults, $result);
            }

        } finally {
            // Bersihkan temp dir
            $this->rrmdir($tempDir);
        }

        return $result;
    }

    private function processShpFile(string $shpFile, array $defaults, array &$result): void
    {
        try {
            $reader = new ShapefileReader($shpFile, [
                Shapefile::OPTION_ENFORCE_POLYGON_CLOSED_RINGS => true,
                Shapefile::OPTION_FORCE_MULTIPART_GEOMETRIES   => false,
                Shapefile::OPTION_SUPPRESS_M                   => true,
                Shapefile::OPTION_SUPPRESS_Z                   => true,
            ]);

            foreach ($reader as $record) {
                if ($record->isEmpty()) {
                    $result['skipped']++;
                    continue;
                }

                try {
                    $geojson = json_decode($record->getGeoJSON(), true);

                    // Validasi koordinat (WGS84 check)
                    if (! $this->isWgs84($geojson)) {
                        $result['errors'][] = 'Koordinat bukan WGS84 (derajat). Harap konversi ke sistem koordinat WGS84 sebelum import.';
                        $result['skipped']++;
                        continue;
                    }

                    // Ambil atribut dari .dbf
                    $dbf = $record->getDataArray();

                    $kodeLahan = $this->extractAttr($dbf, ['kode_lahan', 'kode', 'id_lahan', 'fid'])
                        ?? $this->generateKodeLahan();

                    // Pastikan kode_lahan maks 9 karakter alphanumeric
                    $kodeLahan = preg_replace('/[^a-zA-Z0-9]/', '', (string) $kodeLahan);
                    $kodeLahan = substr($kodeLahan, 0, 9) ?: $this->generateKodeLahan();

                    // Jika kode_lahan sudah ada, skip
                    if (Lahan::where('kode_lahan', $kodeLahan)->exists()) {
                        $kodeLahan = $this->generateKodeLahan();
                    }

                    $pemilik   = $this->extractAttr($dbf, ['pemilik', 'nama_pemilik', 'owner', 'nama'])
                        ?? ($defaults['pemilik'] ?? null);
                    $blokLahan = $this->extractAttr($dbf, ['blok_lahan', 'blok', 'blok_lah'])
                        ?? ($defaults['blok_lahan'] ?? null);
                    $desa      = $this->extractAttr($dbf, ['desa', 'kelurahan', 'village'])
                        ?? ($defaults['desa'] ?? null);
                    $luasLahan = $this->extractAttr($dbf, ['luas_lahan', 'luas', 'area_ha', 'area'])
                        ?? null;
                    $pohonDiDeres = $this->extractAttr($dbf, ['pohon_di_deres', 'pohon', 'jumlah_pohon'])
                        ?? 0;
                    $kelapaBuah   = $this->extractAttr($dbf, ['kelapa_buah', 'kelapa', 'jumlah_kelapa'])
                        ?? 0;

                    Lahan::create([
                        'petani_id'      => $defaults['petani_id'] ?? null,
                        'kode_lahan'     => $kodeLahan,
                        'pemilik'        => $pemilik,
                        'blok_lahan'     => $blokLahan,
                        'desa'           => $desa,
                        'jenis_geometri' => $this->detectGeomType($geojson),
                        'koordinat'      => $geojson,
                        'luas_lahan'     => is_numeric($luasLahan) ? (float) $luasLahan : null,
                        'pohon_di_deres' => is_numeric($pohonDiDeres) ? (int) $pohonDiDeres : 0,
                        'kelapa_buah'    => is_numeric($kelapaBuah)   ? (int) $kelapaBuah   : 0,
                    ]);

                    $result['created']++;

                } catch (\Throwable $e) {
                    $result['errors'][] = 'Record gagal: ' . $e->getMessage();
                    $result['skipped']++;
                }
            }

        } catch (\Throwable $e) {
            $result['errors'][] = 'Gagal membaca SHP: ' . $e->getMessage();
        }
    }

    private function extractAttr(array $dbf, array $keys): mixed
    {
        foreach ($keys as $key) {
            foreach ($dbf as $field => $value) {
                if (strtolower($field) === strtolower($key) && $value !== null && $value !== '') {
                    return $value;
                }
            }
        }
        return null;
    }

    private function generateKodeLahan(): string
    {
        $last = Lahan::orderBy('id', 'desc')->value('kode_lahan');
        $num  = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $num = (int) $m[1] + 1;
        }
        return 'LH' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function isWgs84(array $geojson): bool
    {
        $coord = $this->firstCoord($geojson);
        if ($coord === null) return true; // tidak bisa cek, anggap OK
        // Koordinat WGS84 harus dalam range [-180, 180] untuk lng dan [-90, 90] untuk lat
        return abs($coord[0]) <= 180 && abs($coord[1]) <= 90;
    }

    private function firstCoord(array $geojson): ?array
    {
        $type = $geojson['type'] ?? null;
        if ($type === 'Point')        return $geojson['coordinates'] ?? null;
        if ($type === 'Polygon')      return $geojson['coordinates'][0][0] ?? null;
        if ($type === 'MultiPolygon') return $geojson['coordinates'][0][0][0] ?? null;
        if ($type === 'LineString')   return $geojson['coordinates'][0] ?? null;
        return null;
    }

    private function detectGeomType(array $geojson): string
    {
        $type = strtolower($geojson['type'] ?? '');
        if (str_contains($type, 'point')) return 'titik';
        return 'polygon';
    }

    private function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) return;
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
