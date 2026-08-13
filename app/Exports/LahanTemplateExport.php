<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LahanTemplateExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Data Lahan';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Petani (Farm Code)',
            'Nama Pemilik Lahan',
            'Blok Lahan',
            'Kode Lahan',
            'Desa',
            'Koordinat GPS (lat, lng)',
            'Nama Penderes',
            'Kode Penderes',
            'Luas Lahan (ha)',
            'Pohon di Deres',
            'Kelapa Buah',
        ];
    }

    public function array(): array
    {
        // Baris contoh 1
        return [
            [1, 'BS010001', 'DARTI', 'A', 'BS010001A', 'Bumisari', '-7.3115008, 109.3130626', 'Katini',      'F050001', 0.07, 9,  0],
            [1, 'BS010001', 'DARTI', 'B', 'BS010001B', 'Bumisari', '-7.3129518, 109.3130267', 'Nurhayanto',  'F010010', 0.14, 13, 2],
            [2, 'BS010002', 'RATMONO RATMO', 'A', 'BS010002A', 'Bumisari', '-7.3123648, 109.3125483', 'Ratmono Ratmo', 'F010001', 0.08, 12, 5],
            [3, 'BS010003', 'DARSONO', 'A', 'BS010003A', 'Bumisari', '-7.3164322, 109.3156090', 'Slamet Suparno', 'F020007', 0.18, 7,  2],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 22,
            'C' => 26,
            'D' => 12,
            'E' => 16,
            'F' => 14,
            'G' => 30,
            'H' => 24,
            'I' => 16,
            'J' => 14,
            'K' => 14,
            'L' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;

        // Style header (baris 1)
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E5631']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']],
            ],
        ]);

        // Style data rows
        $sheet->getStyle('A2:L' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Warna baris genap
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']],
                ]);
            }
        }

        // Kolom koordinat — highlight
        $sheet->getStyle('G1:G' . $lastRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFDE7']],
        ]);
        $sheet->getStyle('G1')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E5631']],
        ]);

        // Freeze header
        $sheet->freezePane('A2');

        // Tambah sheet Petunjuk
        $this->addInstructionContent($sheet);

        return [];
    }

    private function addInstructionContent(Worksheet $sheet): void
    {
        $wb = $sheet->getParent();

        // Buat sheet petunjuk
        $info = $wb->createSheet(1);
        $info->setTitle('Petunjuk');

        $instructions = [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT DATA LAHAN', ''],
            ['', ''],
            ['KOLOM', 'KETERANGAN'],
            ['A - No',                   'Nomor urut (opsional)'],
            ['B - Kode Petani',          'Kode unik petani pemilik lahan. Contoh: BS010001'],
            ['C - Nama Pemilik Lahan',   'Nama petani pemilik lahan (huruf kapital dianjurkan)'],
            ['D - Blok Lahan',           'Huruf blok lahan. Contoh: A, B, C, D'],
            ['E - Kode Lahan',           'Kode unik lahan, maks 9 karakter. Contoh: BS010001A'],
            ['F - Desa',                 'Nama desa lokasi lahan'],
            ['G - Koordinat GPS',        'Format: -7.3115008, 109.3130626 (latitude, longitude). Pisahkan dengan koma dan spasi.'],
            ['H - Nama Penderes',        'Nama penderes (penyadap nira). Boleh sama dengan pemilik lahan.'],
            ['I - Kode Penderes',        'Kode unik penderes. Contoh: F050001'],
            ['J - Luas Lahan (ha)',      'Luas lahan dalam hektar. Gunakan titik sebagai pemisah desimal. Contoh: 0.07'],
            ['K - Pohon di Deres',       'Jumlah pohon kelapa yang sedang dideres (disadap)'],
            ['L - Kelapa Buah',          'Jumlah kelapa buah'],
            ['', ''],
            ['CATATAN PENTING', ''],
            ['1.', 'Jangan mengubah nama/urutan kolom pada sheet "Data Lahan"'],
            ['2.', 'Koordinat GPS harus dalam format WGS84 (derajat desimal), bukan UTM'],
            ['3.', 'Baris pertama adalah header — JANGAN dihapus'],
            ['4.', 'Hapus baris contoh sebelum mengisi data sebenarnya'],
            ['5.', 'Jika kolom Kode Lahan sudah ada di database, baris tersebut akan dilewati'],
            ['6.', 'Penderes baru akan otomatis dibuat jika Kode Penderes belum terdaftar'],
        ];

        foreach ($instructions as $i => $row) {
            $info->setCellValue('A' . ($i + 1), $row[0]);
            $info->setCellValue('B' . ($i + 1), $row[1]);
        }

        $info->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E5631']],
        ]);
        $info->getStyle('A3:B3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E5631']],
        ]);
        $info->getStyle('A17')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFCC0000']],
        ]);

        $info->getColumnDimension('A')->setWidth(28);
        $info->getColumnDimension('B')->setWidth(70);
    }
}
