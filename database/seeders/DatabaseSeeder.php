<?php

namespace Database\Seeders;

use App\Models\MasterAsset;
use App\Models\MasterAssetExternal;
use App\Models\MasterLokasiExternal;
use App\Models\RiwayatSo;
use App\Models\RiwayatSoExternal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna Default
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => 'admin123',
                'nama_karyawan' => 'Administrator Utama',
                'jenis_user' => 'ADMINISTRATOR',
            ]
        );

        User::updateOrCreate(
            ['username' => 'akbar'],
            [
                'password' => 'akbar123',
                'nama_karyawan' => 'Akbar Agustiansyah Putra',
                'jenis_user' => 'ADMINISTRATOR',
            ]
        );

        User::updateOrCreate(
            ['username' => 'petugas_internal'],
            [
                'password' => 'petugas123',
                'nama_karyawan' => 'Auditor Internal Palembang',
                'jenis_user' => 'INTERNAL',
            ]
        );

        User::updateOrCreate(
            ['username' => 'petugas_eksternal'],
            [
                'password' => 'eksternal123',
                'nama_karyawan' => 'Auditor Eksternal Vendor',
                'jenis_user' => 'EKSTERNAL',
            ]
        );

        // 2. Master Lokasi External
        MasterLokasiExternal::updateOrCreate(
            ['code_entity' => 'PLG-WH01'],
            ['description' => 'Warehouse Palembang Induk - Area A']
        );

        MasterLokasiExternal::updateOrCreate(
            ['code_entity' => 'PLG-WH02'],
            ['description' => 'Warehouse Palembang Transit - Area B']
        );

        MasterLokasiExternal::updateOrCreate(
            ['code_entity' => 'LPG-DC01'],
            ['description' => 'Distribution Center Lampung']
        );

        MasterLokasiExternal::updateOrCreate(
            ['code_entity' => 'JMB-HUB01'],
            ['description' => 'Hub Logistik Jambi']
        );

        // 3. Master Asset Internal
        $internalAssets = [
            [
                'nomor_asset' => '50000001001',
                'deskripsi_asset' => 'Furniture 1 (Meja & Kursi Kerja)',
                'serial_number' => 'FN-AKB-01',
                'cost_center' => 'CC-ADM-01',
                'qty_buku' => 1,
                'cap_date' => '2023-05-10',
                'nilai_perolehan' => 7500000,
                'akumulasi_depresiasi' => 1500000,
                'nbv' => 6000000,
                'allocation' => 'Office Akbar',
            ],
            [
                'nomor_asset' => '10001001',
                'deskripsi_asset' => 'Forklift Toyota 3-Ton Diesel',
                'serial_number' => 'FL-TOY-2023-8891',
                'cost_center' => 'CC-LOG-01',
                'qty_buku' => 1,
                'cap_date' => '2023-01-15',
                'nilai_perolehan' => 350000000,
                'akumulasi_depresiasi' => 50000000,
                'nbv' => 300000000,
                'allocation' => 'Warehouse Palembang',
            ],
            [
                'nomor_asset' => '10001002',
                'deskripsi_asset' => 'Conveyor Belt Packaging Line 1',
                'serial_number' => 'CV-PKG-001',
                'cost_center' => 'CC-PRD-01',
                'qty_buku' => 1,
                'cap_date' => '2022-06-10',
                'nilai_perolehan' => 120000000,
                'akumulasi_depresiasi' => 30000000,
                'nbv' => 90000000,
                'allocation' => 'Produksi Packaging',
            ],
            [
                'nomor_asset' => '10001003',
                'deskripsi_asset' => 'Mesin Packaging Automatic Sachet',
                'serial_number' => 'PKG-AUTO-992',
                'cost_center' => 'CC-PRD-01',
                'qty_buku' => 1,
                'cap_date' => '2023-08-20',
                'nilai_perolehan' => 280000000,
                'akumulasi_depresiasi' => 35000000,
                'nbv' => 245000000,
                'allocation' => 'Produksi Packaging',
            ],
            [
                'nomor_asset' => '10001004',
                'deskripsi_asset' => 'Server Dell PowerEdge R750 Rackmount',
                'serial_number' => 'SRV-DELL-8821',
                'cost_center' => 'CC-IT-01',
                'qty_buku' => 1,
                'cap_date' => '2023-11-01',
                'nilai_perolehan' => 185000000,
                'akumulasi_depresiasi' => 18500000,
                'nbv' => 166500000,
                'allocation' => 'Data Center Lt 2',
            ],
            [
                'nomor_asset' => '10001005',
                'deskripsi_asset' => 'Laptop Lenovo ThinkPad T14 Gen 3',
                'serial_number' => 'TP-T14-77821',
                'cost_center' => 'CC-IT-01',
                'qty_buku' => 2,
                'cap_date' => '2024-01-10',
                'nilai_perolehan' => 44000000,
                'akumulasi_depresiasi' => 4400000,
                'nbv' => 39600000,
                'allocation' => 'IT Support Office',
            ],
            [
                'nomor_asset' => '10001006',
                'deskripsi_asset' => 'Genset Perkins 500 kVA Silent Type',
                'serial_number' => 'GNS-PRK-500KVA',
                'cost_center' => 'CC-ENG-01',
                'qty_buku' => 1,
                'cap_date' => '2021-04-15',
                'nilai_perolehan' => 620000000,
                'akumulasi_depresiasi' => 155000000,
                'nbv' => 465000000,
                'allocation' => 'Powerhouse Pabrik',
            ],
        ];

        foreach ($internalAssets as $asset) {
            MasterAsset::updateOrCreate(['nomor_asset' => $asset['nomor_asset']], $asset);
        }

        // 4. Master Asset External
        $externalAssets = [
            [
                'nomor_asset' => '20001001',
                'deskripsi_asset' => 'Truck Hino Wingbox 6x2 FL260JW',
                'serial_number' => 'HN-WB-99128',
                'cost_center' => 'CC-DIST-EXT',
                'qty_buku' => 1,
                'cap_date' => '2021-03-20',
                'nilai_perolehan' => 750000000,
                'akumulasi_depresiasi' => 250000000,
                'nbv' => 500000000,
                'allocation' => 'Logistik Eksternal Palembang',
            ],
            [
                'nomor_asset' => '20001002',
                'deskripsi_asset' => 'Chiller Cold Storage Unit 20FT',
                'serial_number' => 'CHL-20FT-991',
                'cost_center' => 'CC-DIST-EXT',
                'qty_buku' => 1,
                'cap_date' => '2022-09-15',
                'nilai_perolehan' => 210000000,
                'akumulasi_depresiasi' => 42000000,
                'nbv' => 168000000,
                'allocation' => 'Distribution Center Lampung',
            ],
            [
                'nomor_asset' => '20001003',
                'deskripsi_asset' => 'Freezer Room Distributor Lampung',
                'serial_number' => 'FRZ-LPG-002',
                'cost_center' => 'CC-DIST-EXT',
                'qty_buku' => 1,
                'cap_date' => '2023-02-10',
                'nilai_perolehan' => 145000000,
                'akumulasi_depresiasi' => 20000000,
                'nbv' => 125000000,
                'allocation' => 'Distribution Center Lampung',
            ],
            [
                'nomor_asset' => '20001004',
                'deskripsi_asset' => 'Electric Pallet Mover Crown PE4500',
                'serial_number' => 'CRW-PE-8821',
                'cost_center' => 'CC-DIST-EXT',
                'qty_buku' => 2,
                'cap_date' => '2023-07-05',
                'nilai_perolehan' => 190000000,
                'akumulasi_depresiasi' => 25000000,
                'nbv' => 165000000,
                'allocation' => 'Warehouse Palembang Transit',
            ],
        ];

        foreach ($externalAssets as $asset) {
            MasterAssetExternal::updateOrCreate(['nomor_asset' => $asset['nomor_asset']], $asset);
        }

        // 5. Riwayat Opname Internal Contoh
        // Cek file foto yang tersedia di storage
        $fotoFisikFiles = glob(storage_path('app/public/opname-photos/*_Fisik_*.jpg'));
        $fotoTagFiles = glob(storage_path('app/public/opname-photos/*_Tag_*.jpg'));

        $sampleFotoFisik = !empty($fotoFisikFiles) ? '/storage/opname-photos/' . basename($fotoFisikFiles[0]) : '';
        $sampleFotoTag = !empty($fotoTagFiles) ? '/storage/opname-photos/' . basename($fotoTagFiles[0]) : '';

        RiwayatSo::updateOrCreate(
            ['nomor_asset' => '50000001001'],
            [
                'timestamp' => Carbon::now(),
                'user' => 'Akbar Agustiansyah Putra',
                'deskripsi_asset' => 'Furniture 1 (Meja & Kursi Kerja)',
                'serial_number' => 'FN-AKB-01',
                'qty_buku' => 1,
                'qty_fisik' => 1,
                'selisih' => 0,
                'tagging' => 'Ada',
                'status_penggunaan' => 'Digunakan',
                'kondisi' => 'Baik',
                'lokasi' => 'Office Akbar',
                'link_foto_fisik' => $sampleFotoFisik,
                'link_tagging_asset' => $sampleFotoTag,
            ]
        );

        RiwayatSo::updateOrCreate(
            ['nomor_asset' => '10001001'],
            [
                'timestamp' => Carbon::now()->subDays(2),
                'user' => 'Auditor Internal Palembang',
                'deskripsi_asset' => 'Forklift Toyota 3-Ton Diesel',
                'serial_number' => 'FL-TOY-2023-8891',
                'qty_buku' => 1,
                'qty_fisik' => 1,
                'selisih' => 0,
                'tagging' => 'Ada',
                'status_penggunaan' => 'Digunakan',
                'kondisi' => 'Baik',
                'lokasi' => 'Warehouse Palembang - Area Loading Bay',
                'link_foto_fisik' => $sampleFotoFisik,
                'link_tagging_asset' => $sampleFotoTag,
            ]
        );

        RiwayatSo::updateOrCreate(
            ['nomor_asset' => '10001002'],
            [
                'timestamp' => Carbon::now()->subDays(1),
                'user' => 'Auditor Internal Palembang',
                'deskripsi_asset' => 'Conveyor Belt Packaging Line 1',
                'serial_number' => 'CV-PKG-001',
                'qty_buku' => 1,
                'qty_fisik' => 1,
                'selisih' => 0,
                'tagging' => 'Ada',
                'status_penggunaan' => 'Digunakan',
                'kondisi' => 'Baik',
                'lokasi' => 'Produksi Packaging Lt 1',
                'link_foto_fisik' => $sampleFotoFisik,
                'link_tagging_asset' => $sampleFotoTag,
            ]
        );

        // 6. Riwayat Opname Eksternal Contoh
        RiwayatSoExternal::updateOrCreate(
            ['nomor_asset' => '20001001'],
            [
                'time_stamps' => Carbon::now()->subDays(3),
                'user' => 'Auditor Eksternal Vendor',
                'deskripsi_asset' => 'Truck Hino Wingbox 6x2 FL260JW',
                'serial_number' => 'HN-WB-99128',
                'aktual_loc' => 'PLG-WH01',
                'book_qty' => 1,
                'physic_qty' => 1,
                'variance' => 0,
                'kelengkapan_tagging' => 'Ada',
                'status' => 'Digunakan',
                'kondisi' => 'Baik',
                'keterangan' => 'Kondisi kendaraan prima, beroperasi normal',
                'foto_fisik' => $sampleFotoFisik,
                'foto_tagging' => $sampleFotoTag,
            ]
        );
    }
}
