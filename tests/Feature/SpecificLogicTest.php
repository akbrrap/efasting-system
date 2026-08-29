<?php

namespace Tests\Feature;

use App\Models\MasterAsset;
use App\Models\MasterAssetExternal;
use App\Models\RiwayatRetirement;
use App\Models\RiwayatSo;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSXGen;
use Tests\TestCase;

class SpecificLogicTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $internalUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::create([
            'username' => 'admin_spec',
            'password' => 'admin123',
            'nama_karyawan' => 'Admin Spec Logic',
            'jenis_user' => 'ADMINISTRATOR',
        ]);

        $this->internalUser = User::create([
            'username' => 'internal_spec',
            'password' => 'pass123',
            'nama_karyawan' => 'Internal Staff',
            'jenis_user' => 'INTERNAL',
        ]);
    }

    public function test_excel_template_download_streams(): void
    {
        $types = ['addition', 'retirement', 'adjustment'];

        foreach ($types as $type) {
            $response = $this->actingAs($this->admin)->get(route('asset.template', $type));
            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
    }

    public function test_backend_mass_addition_processing_xlsx(): void
    {
        $data = [
            ['TITLE BANNER', '', '', '', '', '', '', '', '', '', ''],
            ['INSTRUCTIONS', '', '', '', '', '', '', '', '', '', ''],
            ['KATEGORI_DB', 'NOMOR_ASET', 'DESKRIPSI_ASET', 'SERIAL_NUMBER', 'COST_CENTER', 'QTY_BUKU', 'CAP_DATE', 'NILAI_PEROLEHAN', 'AKUM_DEPRESIASI', 'NBV', 'ALLOCATION'],
            ['INTERNAL', '90001001', 'Mesin Packaging 1', 'SN-PKG-01', 'CC-01', 1, '2024-01-01', 50000000, 5000000, 45000000, 'Palembang'],
            ['EXTERNAL', '90002002', 'Forklift Rental 1', 'SN-FKL-01', 'CC-02', 2, '2024-02-01', 100000000, 10000000, 90000000, 'Distributor A'],
        ];

        $xlsxContent = (string) SimpleXLSXGen::fromArray($data);
        $file = UploadedFile::fake()->createWithContent('mass_addition.xlsx', $xlsxContent);

        $response = $this->actingAs($this->admin)->post(route('asset.mass_addition'), [
            'file' => $file,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'success_count' => 2,
                'skipped_count' => 0,
            ],
        ]);

        $this->assertDatabaseHas('master_asset', [
            'nomor_asset' => '90001001',
            'deskripsi_asset' => 'Mesin Packaging 1',
        ]);

        $this->assertDatabaseHas('master_asset_external', [
            'nomor_asset' => '90002002',
            'deskripsi_asset' => 'Forklift Rental 1',
        ]);
    }

    public function test_backend_mass_retirement_processing_xlsx(): void
    {
        MasterAsset::create([
            'nomor_asset' => '80001001',
            'deskripsi_asset' => 'Genset Cadangan',
            'serial_number' => 'SN-GNS-01',
            'qty_buku' => 2,
            'nilai_perolehan' => 20000000,
            'akumulasi_depresiasi' => 4000000,
            'nbv' => 16000000,
        ]);

        $data = [
            ['TITLE BANNER', '', '', '', ''],
            ['INSTRUCTIONS', '', '', '', ''],
            ['KATEGORI_DB', 'NOMOR_ASET', 'QTY_DISPOSAL', 'DOKUMEN_SAP', 'CATATAN'],
            ['INTERNAL', '80001001', 1, 'SAP-DISP-888', 'Disposal rusak berat'],
        ];

        $xlsxContent = (string) SimpleXLSXGen::fromArray($data);
        $file = UploadedFile::fake()->createWithContent('mass_retirement.xlsx', $xlsxContent);

        $response = $this->actingAs($this->admin)->post(route('asset.mass_retirement'), [
            'file' => $file,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'success_count' => 1,
            ],
        ]);

        $this->assertDatabaseHas('master_asset', [
            'nomor_asset' => '80001001',
            'qty_buku' => 1,
            'nbv' => 8000000,
        ]);

        $this->assertDatabaseHas('riwayat_retirement', [
            'nomor_asset' => '80001001',
            'qty_disposal' => 1,
            'dokumen_sap' => 'SAP-DISP-888',
        ]);
    }

    public function test_backend_photo_upload_via_storage_service(): void
    {
        $service = app(FileStorageService::class);
        $fakePhoto = UploadedFile::fake()->create('kondisi_fisik.jpg', 150, 'image/jpeg');

        $url = $service->storePhoto($fakePhoto, 'TEST_ASSET');

        $this->assertNotNull($url);
        $this->assertStringContainsString('opname-photos/TEST_ASSET_', $url);

        // Test base64 storage
        $base64Sample = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        $base64Url = $service->storePhoto($base64Sample, 'BASE64_ASSET');
        $this->assertNotNull($base64Url);
    }

    public function test_opname_store_with_photo_file_upload(): void
    {
        $photoFisik = UploadedFile::fake()->create('fisik.jpg', 150, 'image/jpeg');
        $photoTagging = UploadedFile::fake()->create('tag.jpg', 150, 'image/jpeg');

        $response = $this->actingAs($this->internalUser)->post(route('opname.internal.store'), [
            'nomor_asset' => '10001001',
            'deskripsi_asset' => 'Server Rack A',
            'serial_number' => 'SRV-01',
            'qty_buku' => 1,
            'qty_fisik' => 1,
            'tagging' => 'Ada',
            'status_penggunaan' => 'Digunakan',
            'kondisi' => 'Baik',
            'lokasi' => 'Data Center Lt 2',
            'foto_fisik' => $photoFisik,
            'foto_tagging' => $photoTagging,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('riwayat_so', [
            'nomor_asset' => '10001001',
            'lokasi' => 'Data Center Lt 2',
        ]);

        $riwayat = RiwayatSo::where('nomor_asset', '10001001')->first();
        $this->assertNotNull($riwayat->link_foto_fisik);
        $this->assertNotNull($riwayat->link_tagging_asset);
    }
}
