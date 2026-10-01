<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Komputer & Laptop',
                'description' => 'Laptop, MacBook, PC Workstation untuk kebutuhan penunjang kerja karyawan.',
            ],
            [
                'name' => 'Monitor & Display',
                'description' => 'Layar monitor eksternal, proyektor, dan TV display ruang rapat.',
            ],
            [
                'name' => 'Smartphone & Tablet',
                'description' => 'Perangkat seluler atau tablet operasional untuk tim lapangan dan QA/Testing.',
            ],
            [
                'name' => 'Furnitur & Kursi Kerja',
                'description' => 'Meja kerja ergonomis, kursi kerja, rak berkas, dan loker karyawan.',
            ],
            [
                'name' => 'Kendaraan Operasional',
                'description' => 'Mobil inventaris dan sepeda motor operasional kantor.',
            ],
            [
                'name' => 'Perangkat Jaringan & IT Support',
                'description' => 'Switch, router, printer multifungsi, UPS, dan perangkat periferal lainnya.',
            ],
        ];

        foreach ($categories as $category) {
            AssetCategory::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
