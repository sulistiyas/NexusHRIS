<?php

namespace Database\Seeders;

use App\Models\ReimbursementCategory;
use Illuminate\Database\Seeder;

class ReimbursementCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Medis & Kacamata',
                'description' => 'Klaim biaya pengobatan, rawat jalan, kacamata, dan vitamin sesuai plafon kesehatan.',
            ],
            [
                'name' => 'Transportasi & BBM',
                'description' => 'Klaim biaya bahan bakar minyak, tol, parkir, dan transportasi tugas luar kantor.',
            ],
            [
                'name' => 'Konsumsi & Representasi Klien',
                'description' => 'Klaim biaya jamuan makan dan pertemuan dinas bersama klien atau mitra kerja.',
            ],
            [
                'name' => 'Pelatihan & Sertifikasi',
                'description' => 'Klaim biaya pendaftaran kursus, seminar, workshop, dan sertifikasi profesi.',
            ],
            [
                'name' => 'Operasional & Perlengkapan Kantor',
                'description' => 'Klaim pembelian perlengkapan kerja kantor darurat atau kebutuhan operasional mendesak.',
            ],
        ];

        foreach ($categories as $category) {
            ReimbursementCategory::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
