<?php

namespace App\Services;

use App\Models\MasterAsset;
use App\Models\MasterAssetExternal;
use App\Models\RiwayatRetirement;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use Symfony\Component\HttpFoundation\Response;

class AssetBulkService
{
    /**
     * Header standar yang diwajibkan untuk setiap jenis template.
     */
    public const ADDITION_HEADERS = [
        "KATEGORI_DB",
        "NOMOR_ASET",
        "DESKRIPSI_ASET",
        "SERIAL_NUMBER",
        "COST_CENTER",
        "QTY_BUKU",
        "CAP_DATE",
        "NILAI_PEROLEHAN",
        "AKUM_DEPRESIASI",
        "NBV",
        "ALLOCATION"
    ];

    public const RETIREMENT_HEADERS = [
        "KATEGORI_DB",
        "NOMOR_ASET",
        "QTY_DISPOSAL",
        "DOKUMEN_SAP",
        "CATATAN"
    ];

    public const ADJUSTMENT_HEADERS = [
        "KATEGORI_DB",
        "NOMOR_ASET",
        "DESKRIPSI_ASET",
        "NILAI_PEROLEHAN",
        "AKUM_DEPRESIASI",
        "NBV"
    ];

    /**
     * Proses Bulk Import Tambah Aset Masal (Mass Addition) dari file Excel (.xlsx) atau CSV.
     */
    public function importAddition(UploadedFile $file): array
    {
        $rows = $this->parseFileRows($file);
        if (count($rows) < 2) {
            throw new \InvalidArgumentException("File Excel/Spreadsheet kosong atau tidak berisi baris data.");
        }

        $headerRowIndex = $this->findHeaderRowIndex($rows, self::ADDITION_HEADERS);
        if ($headerRowIndex === -1) {
            throw new \InvalidArgumentException("Format Header Excel tidak sesuai! Pastikan kolom berurutan: " . implode(", ", self::ADDITION_HEADERS));
        }

        $insertedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row) || !isset($row[0]) || trim((string)$row[0]) === '') {
                    continue;
                }

                $kategori = strtoupper(trim((string)($row[0] ?? '')));
                $nomorAsset = trim((string)($row[1] ?? ''));
                $deskripsi = trim((string)($row[2] ?? ''));
                $serialNumber = trim((string)($row[3] ?? '-'));
                $costCenter = trim((string)($row[4] ?? ''));
                $qtyBuku = (int) ($row[5] ?? 0);
                $capDate = trim((string)($row[6] ?? ''));
                $nilaiPerolehan = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row[7] ?? 0));
                $akumulasiDepresiasi = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row[8] ?? 0));
                $nbv = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row[9] ?? 0));
                $allocation = trim((string)($row[10] ?? ''));

                if (empty($nomorAsset) || empty($deskripsi) || $qtyBuku < 1) {
                    $skippedCount++;
                    continue;
                }

                // Cek duplikasi nomor aset
                $exists = ($kategori === 'INTERNAL')
                    ? MasterAsset::where('nomor_asset', $nomorAsset)->exists()
                    : MasterAssetExternal::where('nomor_asset', $nomorAsset)->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                $parsedDate = null;
                if (!empty($capDate)) {
                    try {
                        $parsedDate = Carbon::parse($capDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $parsedDate = null;
                    }
                }

                $data = [
                    'nomor_asset' => $nomorAsset,
                    'deskripsi_asset' => $deskripsi,
                    'serial_number' => $serialNumber ?: '-',
                    'cost_center' => $costCenter,
                    'qty_buku' => $qtyBuku,
                    'cap_date' => $parsedDate,
                    'nilai_perolehan' => $nilaiPerolehan,
                    'akumulasi_depresiasi' => $akumulasiDepresiasi,
                    'nbv' => $nbv,
                    'allocation' => $allocation,
                ];

                if ($kategori === 'INTERNAL') {
                    MasterAsset::create($data);
                } else {
                    MasterAssetExternal::create($data);
                }

                $insertedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        if ($insertedCount === 0 && $skippedCount > 0) {
            throw new \InvalidArgumentException("Semua ({$skippedCount}) baris data di file Excel dilewati karena nomor aset sudah terdaftar atau data tidak lengkap.");
        }

        return [
            'success_count' => $insertedCount,
            'skipped_count' => $skippedCount,
            'total_processed' => $insertedCount + $skippedCount,
        ];
    }

    /**
     * Proses Bulk Import Disposal Masal (Mass Retirement) dari file Excel.
     */
    public function importRetirement(UploadedFile $file, ?string $petugas = null): array
    {
        $rows = $this->parseFileRows($file);
        if (count($rows) < 2) {
            throw new \InvalidArgumentException("File Excel kosong.");
        }

        $headerRowIndex = $this->findHeaderRowIndex($rows, self::RETIREMENT_HEADERS);
        if ($headerRowIndex === -1) {
            throw new \InvalidArgumentException("Format Header Excel tidak sesuai! Pastikan kolom berurutan: " . implode(", ", self::RETIREMENT_HEADERS));
        }

        $successCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row) || !isset($row[0]) || trim((string)$row[0]) === '') {
                    continue;
                }

                $kategori = strtoupper(trim((string)($row[0] ?? '')));
                $nomorAsset = trim((string)($row[1] ?? ''));
                $qtyDisposal = (int) ($row[2] ?? 0);
                $dokumenSap = trim((string)($row[3] ?? '-'));
                $catatan = trim((string)($row[4] ?? '-'));

                if (empty($nomorAsset) || $qtyDisposal <= 0) {
                    $skippedCount++;
                    continue;
                }

                $asset = ($kategori === 'INTERNAL')
                    ? MasterAsset::where('nomor_asset', $nomorAsset)->first()
                    : MasterAssetExternal::where('nomor_asset', $nomorAsset)->first();

                if (!$asset || $qtyDisposal > $asset->qty_buku) {
                    $skippedCount++;
                    continue;
                }

                $rasio = $qtyDisposal / $asset->qty_buku;
                $nbvPotong = round($asset->nbv * $rasio, 2);
                $npPotong = round($asset->nilai_perolehan * $rasio, 2);
                $adPotong = round($asset->akumulasi_depresiasi * $rasio, 2);

                RiwayatRetirement::create([
                    'petugas' => $petugas ?? auth()->user()->nama_karyawan ?? 'System',
                    'kategori_db' => $kategori,
                    'nomor_asset' => $nomorAsset,
                    'deskripsi_asset' => $asset->deskripsi_asset,
                    'qty_disposal' => $qtyDisposal,
                    'nbv_disposal' => $nbvPotong,
                    'dokumen_sap' => $dokumenSap,
                    'catatan' => $catatan,
                ]);

                if ($qtyDisposal === $asset->qty_buku) {
                    $asset->delete();
                } else {
                    $asset->update([
                        'qty_buku' => $asset->qty_buku - $qtyDisposal,
                        'nilai_perolehan' => $asset->nilai_perolehan - $npPotong,
                        'akumulasi_depresiasi' => $asset->akumulasi_depresiasi - $adPotong,
                        'nbv' => $asset->nbv - $nbvPotong,
                    ]);
                }

                $successCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        if ($successCount === 0) {
            throw new \InvalidArgumentException("Tidak ada data valid yang dapat diproses. Pastikan nomor aset ada di database dan kuantiti disposal valid.");
        }

        return [
            'success_count' => $successCount,
            'skipped_count' => $skippedCount,
            'total_processed' => $successCount + $skippedCount,
        ];
    }

    /**
     * Proses Bulk Import Penyesuaian Nilai Finansial (Mass Adjustment) dari file Excel.
     */
    public function importAdjustment(UploadedFile $file): array
    {
        $rows = $this->parseFileRows($file);
        if (count($rows) < 2) {
            throw new \InvalidArgumentException("File Excel kosong.");
        }

        $headerRowIndex = $this->findHeaderRowIndex($rows, self::ADJUSTMENT_HEADERS);
        if ($headerRowIndex === -1) {
            throw new \InvalidArgumentException("Format Header Excel tidak sesuai!");
        }

        $successCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row) || !isset($row[0]) || trim((string)$row[0]) === '') {
                    continue;
                }

                $kategori = strtoupper(trim((string)($row[0] ?? '')));
                $nomorAsset = trim((string)($row[1] ?? ''));
                $nilaiPerolehan = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row[3] ?? 0));
                $akumulasiDepresiasi = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row[4] ?? 0));
                $nbv = (float) str_replace(['.', ',', 'Rp', ' '], ['', '.', '', ''], (string)($row[5] ?? 0));

                if (empty($nomorAsset)) {
                    $skippedCount++;
                    continue;
                }

                $asset = ($kategori === 'INTERNAL')
                    ? MasterAsset::where('nomor_asset', $nomorAsset)->first()
                    : MasterAssetExternal::where('nomor_asset', $nomorAsset)->first();

                if (!$asset) {
                    $skippedCount++;
                    continue;
                }

                $asset->update([
                    'nilai_perolehan' => $nilaiPerolehan,
                    'akumulasi_depresiasi' => $akumulasiDepresiasi,
                    'nbv' => $nbv,
                ]);

                $successCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success_count' => $successCount,
            'skipped_count' => $skippedCount,
            'total_processed' => $successCount + $skippedCount,
        ];
    }

    /**
     * Generator Template Resmi Microsoft Excel (.xlsx) dengan Warna Brand & Proteksi Header.
     */
    public function generateTemplate(string $type, string $kategori = 'INTERNAL'): Response
    {
        $type = strtolower($type);
        $fileName = "Template_Mass_{$type}.xlsx";
        $data = [];

        if ($type === 'addition') {
            // Row 1: Title Banner
            $data[] = [
                '<style bgcolor="#004B87" color="#FFFFFF" font-size="12"><b>TEMPLATE MASS ADDITION - eFASTING ENTERPRISE</b></style>',
                '', '', '', '', '', '', '', '', '', ''
            ];
            // Row 2: Instructions (Locked guide)
            $data[] = [
                '<style bgcolor="#E6F0FA" color="#004B87"><b>PETUNJUK:</b> Isi KATEGORI_DB (INTERNAL / EXTERNAL). DILARANG mengubah nama kolom header di bawah ini. Isi data mulai baris ke-4.</style>',
                '', '', '', '', '', '', '', '', '', ''
            ];
            // Row 3: Table Headers
            $headers = [];
            foreach (self::ADDITION_HEADERS as $h) {
                $headers[] = '<style bgcolor="#004B87" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $data[] = $headers;

            // Row 4: Sample data
            $data[] = [
                '<style border="thin" bgcolor="#FFF9E6">INTERNAL</style>',
                '<style border="thin">10009001</style>',
                '<style border="thin">Mesin Produksi Sample</style>',
                '<style border="thin">SN-999-XYZ</style>',
                '<style border="thin">CC-LOG-01</style>',
                '<style border="thin">1</style>',
                '<style border="thin">2024-01-15</style>',
                '<style border="thin">50000000</style>',
                '<style border="thin">5000000</style>',
                '<style border="thin">45000000</style>',
                '<style border="thin">Palembang</style>',
            ];

        } elseif ($type === 'retirement') {
            // Row 1: Title Banner
            $data[] = [
                '<style bgcolor="#C0392B" color="#FFFFFF" font-size="12"><b>TEMPLATE MASS RETIREMENT / DISPOSAL - eFASTING ENTERPRISE</b></style>',
                '', '', '', ''
            ];
            // Row 2: Instructions
            $data[] = [
                '<style bgcolor="#FDEDEC" color="#922B21"><b>PETUNJUK:</b> Isi nomor aset yang akan dihapus/dipotong stoknya beserta Dokumen SAP dan catatan disposal. DILARANG mengubah baris header.</style>',
                '', '', '', ''
            ];
            // Row 3: Table Headers
            $headers = [];
            foreach (self::RETIREMENT_HEADERS as $h) {
                $headers[] = '<style bgcolor="#C0392B" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $data[] = $headers;

            // Row 4: Sample data
            $data[] = [
                '<style border="thin" bgcolor="#FDEDEC">INTERNAL</style>',
                '<style border="thin">10001001</style>',
                '<style border="thin">1</style>',
                '<style border="thin">SAP-DISP-2026-001</style>',
                '<style border="thin">Afkir / Kerusakan Mesin Fatal</style>',
            ];

        } elseif ($type === 'adjustment') {
            // Row 1: Title Banner
            $data[] = [
                '<style bgcolor="#D68910" color="#FFFFFF" font-size="12"><b>TEMPLATE MASS ASSET ADJUSTMENT - eFASTING ENTERPRISE</b></style>',
                '', '', '', '', ''
            ];
            // Row 2: Instructions
            $data[] = [
                '<style bgcolor="#FEF9E7" color="#7D6608"><b>PETUNJUK:</b> Perbarui nilai perolehan, akumulasi depresiasi, dan NBV aset. DILARANG mengubah urutan kolom header.</style>',
                '', '', '', '', ''
            ];
            // Row 3: Table Headers
            $headers = [];
            foreach (self::ADJUSTMENT_HEADERS as $h) {
                $headers[] = '<style bgcolor="#D68910" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $data[] = $headers;

            // Populate current assets
            $assets = ($kategori === 'INTERNAL')
                ? MasterAsset::limit(25)->get()
                : MasterAssetExternal::limit(25)->get();

            if ($assets->isEmpty()) {
                $data[] = [
                    '<style border="thin">' . $kategori . '</style>',
                    '<style border="thin">10001001</style>',
                    '<style border="thin">Sample Asset Description</style>',
                    '<style border="thin">10000000</style>',
                    '<style border="thin">2000000</style>',
                    '<style border="thin">8000000</style>',
                ];
            } else {
                foreach ($assets as $a) {
                    $data[] = [
                        '<style border="thin">' . $kategori . '</style>',
                        '<style border="thin">' . $a->nomor_asset . '</style>',
                        '<style border="thin">' . $a->deskripsi_asset . '</style>',
                        '<style border="thin">' . $a->nilai_perolehan . '</style>',
                        '<style border="thin">' . $a->akumulasi_depresiasi . '</style>',
                        '<style border="thin">' . $a->nbv . '</style>',
                    ];
                }
            }
        }

        $xlsx = SimpleXLSXGen::fromArray($data);
        $xlsxContent = (string) $xlsx;

        return response($xlsxContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Content-Length' => strlen($xlsxContent),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Membaca file Excel (.xlsx, .xls) atau CSV menjadi array baris.
     */
    private function parseFileRows(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        // 1. Jika file bertipe Excel (.xlsx / .xls)
        if (in_array($ext, ['xlsx', 'xls']) || $file->getClientMimeType() === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            if ($xlsx = SimpleXLSX::parse($path)) {
                return $xlsx->rows();
            }
        }

        // 2. Fallback / CSV parser
        try {
            $reader = Reader::createFromPath($path, 'r');
            $reader->setHeaderOffset(null);
            $rows = [];
            foreach ($reader->getRecords() as $record) {
                $rows[] = array_map('trim', $record);
            }
            return $rows;
        } catch (\Exception $e) {
            // Jika parsing CSV gagal, coba baca via SimpleXLSX
            if ($xlsx = SimpleXLSX::parse($path)) {
                return $xlsx->rows();
            }
            throw new \InvalidArgumentException("Format file tidak dapat dibaca. Pastikan file berformat .xlsx atau .csv valid.");
        }
    }

    /**
     * Mencari baris ke berapa yang merupakan baris header.
     */
    private function findHeaderRowIndex(array $rows, array $expectedHeaders): int
    {
        foreach ($rows as $index => $row) {
            if (empty($row)) continue;
            
            $matched = 0;
            for ($c = 0; $c < min(count($row), count($expectedHeaders)); $c++) {
                $cellValue = strtoupper(trim(strip_tags((string)$row[$c])));
                if ($cellValue === strtoupper($expectedHeaders[$c])) {
                    $matched++;
                }
            }

            if ($matched >= 3) {
                return $index;
            }
        }

        return -1;
    }
}
