<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'koefisien_normal_kg_per_pohon', 'value' => '0.5'],
            ['key' => 'koefisien_max_kg_per_pohon',    'value' => '0.75'],
            ['key' => 'nama_kub',                       'value' => 'KUB Gula Kelapa Organik'],
            ['key' => 'alamat_kub',                     'value' => '-'],
        ];

        foreach ($defaults as $item) {
            Setting::firstOrCreate(['key' => $item['key']], ['value' => $item['value']]);
        }
    }
}
