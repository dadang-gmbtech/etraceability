<?php

namespace Database\Seeders;

use App\Models\Petani;
use App\Models\Lahan;
use App\Models\Pengepul;
use App\Models\Distributor;
use App\Models\BatchProduksi;
use App\Models\SetoranGula;
use App\Models\SertifikasiOrganik;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Koefisien Anomali
        Setting::create([
            'key' => 'koefisien_maks_gula_per_pohon',
            'value' => '0.75'
        ]);

        // Buat User Admin
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin eTraceability',
                'password' => bcrypt('password')
            ]
        );

        // 2. Buat Pengepul & Distributor
        $pengepul1 = Pengepul::create([
            'kode_pengepul' => 'PPL-0001',
            'nama_koperasi' => 'KUB Maju Bersama',
            'penanggung_jawab' => 'Bapak Wahyudi',
            'no_hp' => '081355556666',
            'alamat' => 'Wates, Kulon Progo',
        ]);

        Distributor::create([
            'kode_distributor' => 'DST-0001',
            'nama_perusahaan' => 'CV Manis Nusantara',
            'no_hp' => '081377778888',
            'alamat' => 'Yogyakarta',
        ]);

        // 3. Buat Petani Agus
        $petaniAgus = Petani::create([
            'kode_petani' => 'PTN-0001',
            'nama' => 'Agus',
            'desa' => 'Hargotirto',
            'kecamatan' => 'Kokap',
            'kabupaten' => 'Kulon Progo',
            'aktif' => true,
        ]);
        SertifikasiOrganik::create([
            'petani_id' => $petaniAgus->id,
            'nomor_sertifikat' => 'LSO-2026-00123',
            'lembaga_sertifikasi' => 'INOFICE',
            'tanggal_terbit' => now()->subYear(),
            'tanggal_kadaluarsa' => now()->addYears(2),
            'status' => 'aktif',
        ]);
        
        // Lahan Agus (Lahan A: 7 pohon)
        $lahanA = Lahan::create([
            'petani_id' => $petaniAgus->id,
            'nama_lahan' => 'Lahan A (Agus)',
            'jumlah_pohon' => 7,
        ]);
        // Set point
        $lahanA->setGeomPoint(-7.7895, 110.1234);

        // 4. Buat Petani Budi
        $petaniBudi = Petani::create([
            'kode_petani' => 'PTN-0002',
            'nama' => 'Budi',
            'desa' => 'Purwoharjo',
            'kecamatan' => 'Samigaluh',
            'kabupaten' => 'Kulon Progo',
            'aktif' => true,
        ]);
        
        // Lahan Budi (Lahan B: 5 pohon, Lahan C: 4 pohon)
        $lahanB = Lahan::create([
            'petani_id' => $petaniBudi->id,
            'nama_lahan' => 'Lahan B (Budi)',
            'jumlah_pohon' => 5,
        ]);
        $lahanB->setGeomPolygon([
            [-7.6987, 110.1456],
            [-7.6980, 110.1460],
            [-7.6990, 110.1465]
        ]);

        $lahanC = Lahan::create([
            'petani_id' => $petaniBudi->id,
            'nama_lahan' => 'Lahan C (Budi)',
            'jumlah_pohon' => 4,
        ]);
        $lahanC->setGeomPoint(-7.7000, 110.1470);

        // 5. Buat Batch Produksi (Gabungan Agus dan Budi)
        $batch = BatchProduksi::create([
            'trace_id' => BatchProduksi::generateTraceId(),
            'pengepul_id' => $pengepul1->id,
            'tanggal_pengumpulan' => now(),
            'status_batch' => 'dikumpulkan',
            'is_organik' => true,
            'berat_total_kg' => 0 // Diisi nanti
        ]);

        // 6. Setoran Agus (Normal)
        // 7 pohon * 0.75 kg * 3 hari = max 15.75 kg
        // Setor 10 kg (Aman)
        SetoranGula::create([
            'batch_produksi_id' => $batch->id,
            'petani_id' => $petaniAgus->id,
            'berat_kg' => 10.0,
            'tanggal_setor' => now(),
            'hari_akumulasi' => 3,
            'is_anomali' => false,
        ]);

        // 7. Setoran Budi (Anomali)
        // Budi punya (5+4) = 9 pohon. 
        // 9 pohon * 0.75 kg * 1 hari = max 6.75 kg.
        // Setor 15 kg (Anomali karena melebihi batas 1 hari!)
        SetoranGula::create([
            'batch_produksi_id' => $batch->id,
            'petani_id' => $petaniBudi->id,
            'berat_kg' => 15.0,
            'tanggal_setor' => now(),
            'hari_akumulasi' => 1,
            'is_anomali' => true,
            'keterangan_anomali' => 'Produksi 15kg melebihi batas wajar 6.75kg (9 pohon x 1 hari)',
        ]);

        // Update total berat batch
        $batch->update(['berat_total_kg' => 10.0 + 15.0]);

        $this->command->info("Demo seeder selesai. Trace ID: {$batch->trace_id}");
    }
}
