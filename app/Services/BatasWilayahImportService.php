<?php

namespace App\Services;

use App\Models\BatasWilayah;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BatasWilayahImportService
{
    /**
     * Import batas wilayah records dari ZIP shapefile.
     *
     * @param  string  $zipStoragePath  Path relatif di storage (dari Filament FileUpload)
     * @param  string  $jenis           'kecamatan' atau 'desa'
     * @return array   ['created' => int, 'skipped' => int, 'errors' => string[]]
     */
    public function importFromZip(string $zipStoragePath, string $jenis): array
    {
        $result = ['created' => 0, 'skipped' => 0, 'errors' => []];

        $zipFullPath = Storage::disk('local')->path($zipStoragePath);
        if (! file_exists($zipFullPath)) {
            $result['errors'][] = 'File ZIP tidak ditemukan.';
            return $result;
        }

        $tempDir = sys_get_temp_dir() . '/batas_import_' . uniqid();
        mkdir($tempDir, 0755, true);

        try {
            $zip = new ZipArchive();
            if ($zip->open($zipFullPath) !== true) {
                $result['errors'][] = 'Gagal membuka ZIP.';
                return $result;
            }
            $zip->extractTo($tempDir);
            $zip->close();

            $shpFiles = array_merge(
                glob($tempDir . '/**/*.shp') ?: [],
                glob($tempDir . '/*.shp') ?: []
            );

            if (empty($shpFiles)) {
                $result['errors'][] = 'Tidak ditemukan file .shp di dalam ZIP.';
                return $result;
            }

            foreach ($shpFiles as $shpFile) {
                $this->processShpFile($shpFile, $jenis, $result);
            }
        } finally {
            $this->rrmdir($tempDir);
        }

        return $result;
    }

    private function processShpFile(string $shpFile, string $jenis, array &$result): void
    {
        try {
            $reader = new ShapefileReader($shpFile, [
                Shapefile::OPTION_ENFORCE_POLYGON_CLOSED_RINGS => true,
                Shapefile::OPTION_FORCE_MULTIPART_GEOMETRIES   => false,
                Shapefile::OPTION_SUPPRESS_M                   => true,
                Shapefile::OPTION_SUPPRESS_Z                   => true,
            ]);
        } catch (\Throwable $e) {
            $result['errors'][] = 'Gagal membuka SHP: ' . $e->getMessage();
            return;
        }

        foreach ($reader as $record) {
            if ($record->isEmpty()) {
                $result['skipped']++;
                continue;
            }

            try {
                $geojson = json_decode($record->getGeoJSON(), true);

                if (! $this->isWgs84($geojson)) {
                    $result['errors'][] = 'Koordinat bukan WGS84. Harap konversi terlebih dahulu.';
                    $result['skipped']++;
                    continue;
                }

                try {
                    $dbf = $record->getDataArray();
                } catch (\Throwable $e) {
                    $dbf = [];
                }

                $nama = $this->extractAttr($dbf, [
                    'nama', 'name', 'NAMOBJ', 'NAMKEC', 'NAMDESA',
                    'DESA', 'KECAMATAN', 'WADMKC', 'WADMKD',
                ]) ?? 'Tanpa Nama';

                $kode = $this->extractAttr($dbf, [
                    'kode', 'kd_kec', 'kd_desa', 'KDKEC', 'KDDESA',
                    'OBJECTID', 'FID', 'ID',
                ]);

                if (BatasWilayah::where('jenis', $jenis)->where('nama', $nama)->exists()) {
                    $result['skipped']++;
                    continue;
                }

                BatasWilayah::create([
                    'jenis'     => $jenis,
                    'nama'      => $nama,
                    'kode'      => $kode,
                    'koordinat' => $geojson,
                ]);

                $result['created']++;

            } catch (\Throwable $e) {
                $result['errors'][] = 'Record gagal: ' . $e->getMessage();
                $result['skipped']++;
            }
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

    private function isWgs84(array $geojson): bool
    {
        $coord = $this->firstCoord($geojson);
        if ($coord === null) return true;
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
