<?php

namespace Tests\Feature;

use App\Models\MasterAsset;
use App\Models\MasterAssetExternal;
use App\Models\MasterLokasiExternal;
use App\Models\RiwayatSo;
use App\Models\RiwayatSoExternal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugasInternal;
    protected User $petugasExternal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'username' => 'admin_user',
            'password' => 'admin123',
            'nama_karyawan' => 'Admin Controller Test',
            'jenis_user' => 'ADMINISTRATOR',
        ]);

        $this->petugasInternal = User::create([
            'username' => 'internal_user',
            'password' => 'pass123',
            'nama_karyawan' => 'Petugas Internal Test',
            'jenis_user' => 'INTERNAL',
        ]);

        $this->petugasExternal = User::create([
            'username' => 'external_user',
            'password' => 'pass123',
            'nama_karyawan' => 'Petugas External Test',
            'jenis_user' => 'EKSTERNAL',
        ]);
    }

    public function test_dashboard_metrics_calculation_and_api(): void
    {
        // Buat record opname internal
        RiwayatSo::create([
            'nomor_asset' => 'ASET-001',
            'deskripsi_asset' => 'Laptop Dell XPS',
            'qty_buku' => 1,
            'qty_fisik' => 1,
            'selisih' => 0,
            'tagging' => 'Ada',
            'status_penggunaan' => 'Digunakan',
            'kondisi' => 'Baik',
            'lokasi' => 'Ruang IT',
        ]);

        // Buat record opname eksternal
        RiwayatSoExternal::create([
            'nomor_asset' => 'ASET-EXT-001',
            'deskripsi_asset' => 'Truck Hino',
            'book_qty' => 1,
            'physic_qty' => 1,
            'variance' => 0,
            'kelengkapan_tagging' => 'Ada',
            'status' => 'Digunakan',
            'kondisi' => 'Baik',
            'aktual_loc' => 'Warehouse Palembang',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/dashboard/stats');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'totalInternal' => 1,
                'totalExternal' => 1,
                'tagAda' => 2,
                'tagTidak' => 0,
                'konBaik' => 2,
                'statGuna' => 2,
            ]
        ]);
    }

    public function test_asset_addition_and_search_api(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/assets', [
            'kategori_db' => 'INTERNAL',
            'nomor_asset' => '10009999',
            'deskripsi_asset' => 'Mesin Packaging Baru',
            'serial_number' => 'SN-PKG-99',
            'cost_center' => 'CC-PRD-02',
            'qty_buku' => 2,
            'cap_date' => '2024-01-01',
            'nilai_perolehan' => 50000000,
            'akumulasi_depresiasi' => 5000000,
            'nbv' => 45000000,
            'allocation' => 'Produksi B',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('master_asset', ['nomor_asset' => '10009999']);

        // Search lookup API
        $searchRes = $this->actingAs($this->admin)->getJson('/api/assets/search?query=10009999');
        $searchRes->assertStatus(200);
        $searchRes->assertJsonFragment(['id' => '10009999']);
    }

    public function test_asset_retirement_flow(): void
    {
        $asset = MasterAsset::create([
            'nomor_asset' => 'DISP-001',
            'deskripsi_asset' => 'Forklift Rusak',
            'qty_buku' => 2,
            'nilai_perolehan' => 100000000,
            'akumulasi_depresiasi' => 60000000,
            'nbv' => 40000000,
        ]);

        // Disposal 1 unit
        $response = $this->actingAs($this->admin)->postJson('/assets/retirement', [
            'kategori_db' => 'INTERNAL',
            'nomor_asset' => 'DISP-001',
            'qty_disposal' => 1,
            'dokumen_sap' => 'SAP-DISP-2026-001',
            'catatan' => 'Kerusakan transmisi fatal',
        ]);

        $response->assertStatus(200);

        // Cek log riwayat retirement
        $this->assertDatabaseHas('riwayat_retirement', [
            'nomor_asset' => 'DISP-001',
            'qty_disposal' => 1,
            'nbv_disposal' => 20000000,
        ]);

        // Cek sisa stok di master_asset
        $this->assertDatabaseHas('master_asset', [
            'nomor_asset' => 'DISP-001',
            'qty_buku' => 1,
            'nbv' => 20000000,
        ]);
    }

    public function test_opname_internal_and_external_store(): void
    {
        // Internal
        $resInt = $this->actingAs($this->petugasInternal)->postJson('/opname/internal', [
            'nomor_asset' => '10001001',
            'deskripsi_asset' => 'Forklift Toyota',
            'qty_buku' => 1,
            'qty_fisik' => 1,
            'tagging' => 'Ada',
            'status_penggunaan' => 'Digunakan',
            'kondisi' => 'Baik',
            'lokasi' => 'Ruang Logistik A',
        ]);
        $resInt->assertStatus(200);
        $this->assertDatabaseHas('riwayat_so', ['nomor_asset' => '10001001']);

        // External
        $resExt = $this->actingAs($this->petugasExternal)->postJson('/opname/external', [
            'nomor_asset' => '20001001',
            'deskripsi_asset' => 'Truck Hino',
            'book_qty' => 1,
            'physic_qty' => 1,
            'kelengkapan_tagging' => 'Ada',
            'status' => 'Digunakan',
            'kondisi' => 'Baik',
            'aktual_loc' => 'PLG-WH01',
            'keterangan' => 'Kondisi prima',
        ]);
        $resExt->assertStatus(200);
        $this->assertDatabaseHas('riwayat_so_external', ['nomor_asset' => '20001001']);
    }

    public function test_role_authorization_restrictions(): void
    {
        // Petugas Eksternal dilarang mengakses form opname internal (403)
        $res = $this->actingAs($this->petugasExternal)->get('/opname/internal');
        $res->assertStatus(403);

        // Petugas Internal dilarang mengakses menu Master Assets (403)
        $resMaster = $this->actingAs($this->petugasInternal)->get('/assets');
        $resMaster->assertStatus(403);
    }
}
