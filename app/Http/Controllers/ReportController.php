<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSo;
use App\Models\RiwayatSoExternal;
use App\Services\FileStorageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Shuchkin\SimpleXLSXGen;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class ReportController extends Controller
{
    protected FileStorageService $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Tampilkan halaman Export Laporan Audit Opname.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Export Data Laporan ke Microsoft Excel (.xlsx) yang dibundel bersama foto fisik & tagging dalam format ZIP.
     */
    public function export(Request $request): JsonResponse|Response|BinaryFileResponse
    {
        $validated = $request->validate([
            'kategori' => 'required|in:INTERNAL,EXTERNAL',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'nullable|string|in:json,excel,xlsx,zip,all',
        ]);

        $kategori = strtoupper($validated['kategori']);
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $format = strtolower($validated['format'] ?? 'zip');

        if ($kategori === 'INTERNAL') {
            $query = RiwayatSo::query();
            if ($startDate && $endDate) {
                $query->whereBetween('timestamp', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }
            $data = $query->orderBy('timestamp', 'desc')->get();
        } else {
            $query = RiwayatSoExternal::query();
            if ($startDate && $endDate) {
                $query->whereBetween('time_stamps', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }
            $data = $query->orderBy('time_stamps', 'desc')->get();
        }

        if ($format === 'json' || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'kategori' => $kategori,
                'total' => $data->count(),
                'data' => $data,
            ]);
        }

        // 1. Generate Microsoft Excel (.xlsx)
        $excelRows = [];
        $periode = ($startDate && $endDate) ? "{$startDate} s/d {$endDate}" : "Semua Periode";
        $timestampStr = date('Ymd_His');

        if ($kategori === 'INTERNAL') {
            $excelBaseName = "Laporan_Opname_Internal_{$timestampStr}.xlsx";

            // Row 1: Title
            $excelRows[] = [
                '<style bgcolor="#004B87" color="#FFFFFF" font-size="12"><b>LAPORAN STOCK OPNAME ASSET INTERNAL - eFASTING</b></style>',
                '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];
            // Row 2: Metadata Info
            $excelRows[] = [
                "<style bgcolor=\"#E6F0FA\" color=\"#004B87\"><b>Periode:</b> {$periode} | <b>Total Data:</b> {$data->count()} Record | <b>Tanggal Unduh:</b> " . date('d/m/Y H:i') . "</style>",
                '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            // Row 3: Headers
            $headers = [
                "TIMESTAMP", "USER / PETUGAS", "NOMOR_ASET", "DESKRIPSI_ASET",
                "SERIAL_NUMBER", "QTY_BUKU", "QTY_FISIK", "SELISIH",
                "TAGGING", "STATUS_PENGGUNAAN", "KONDISI", "LOKASI",
                "FOTO_FISIK_FILE", "FOTO_TAGGING_FILE"
            ];
            $headerFormatted = [];
            foreach ($headers as $h) {
                $headerFormatted[] = '<style bgcolor="#004B87" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $excelRows[] = $headerFormatted;

            // Data Rows
            foreach ($data as $idx => $row) {
                $selisihStyle = ($row->selisih != 0) ? '<style border="thin" color="#C0392B"><b>' . $row->selisih . '</b></style>' : '<style border="thin">' . $row->selisih . '</style>';
                
                $fisikFile = $row->link_foto_fisik ? "foto_fisik/{$row->nomor_asset}_Fisik_" . ($row->id ?? ($idx + 1)) . ".jpg" : "-";
                $tagFile = $row->link_tagging_asset ? "foto_tagging/{$row->nomor_asset}_Tagging_" . ($row->id ?? ($idx + 1)) . ".jpg" : "-";

                $excelRows[] = [
                    '<style border="thin">' . ($row->timestamp ? $row->timestamp->format('Y-m-d H:i:s') : '') . '</style>',
                    '<style border="thin">' . $row->user . '</style>',
                    '<style border="thin">' . $row->nomor_asset . '</style>',
                    '<style border="thin">' . $row->deskripsi_asset . '</style>',
                    '<style border="thin">' . ($row->serial_number ?? '-') . '</style>',
                    '<style border="thin">' . $row->qty_buku . '</style>',
                    '<style border="thin">' . $row->qty_fisik . '</style>',
                    $selisihStyle,
                    '<style border="thin">' . $row->tagging . '</style>',
                    '<style border="thin">' . $row->status_penggunaan . '</style>',
                    '<style border="thin">' . $row->kondisi . '</style>',
                    '<style border="thin">' . $row->lokasi . '</style>',
                    '<style border="thin">' . $fisikFile . '</style>',
                    '<style border="thin">' . $tagFile . '</style>',
                ];
            }
        } else {
            $excelBaseName = "Laporan_Opname_External_{$timestampStr}.xlsx";

            // Row 1: Title
            $excelRows[] = [
                '<style bgcolor="#004B87" color="#FFFFFF" font-size="12"><b>LAPORAN STOCK OPNAME ASSET EXTERNAL - eFASTING</b></style>',
                '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];
            // Row 2: Metadata Info
            $excelRows[] = [
                "<style bgcolor=\"#E6F0FA\" color=\"#004B87\"><b>Periode:</b> {$periode} | <b>Total Data:</b> {$data->count()} Record | <b>Tanggal Unduh:</b> " . date('d/m/Y H:i') . "</style>",
                '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            // Row 3: Headers
            $headers = [
                "TIME_STAMPS", "USER / PETUGAS", "NOMOR_ASET", "DESKRIPSI_ASET",
                "SERIAL_NUMBER", "AKTUAL_LOC", "BOOK_QTY", "PHYSIC_QTY",
                "VARIANCE", "TAGGING", "STATUS", "KONDISI",
                "KETERANGAN", "FOTO_FISIK_FILE", "FOTO_TAGGING_FILE"
            ];
            $headerFormatted = [];
            foreach ($headers as $h) {
                $headerFormatted[] = '<style bgcolor="#004B87" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $excelRows[] = $headerFormatted;

            // Data Rows
            foreach ($data as $idx => $row) {
                $varianceStyle = ($row->variance != 0) ? '<style border="thin" color="#C0392B"><b>' . $row->variance . '</b></style>' : '<style border="thin">' . $row->variance . '</style>';

                $fisikFile = $row->foto_fisik ? "foto_fisik/{$row->nomor_asset}_Fisik_" . ($row->id ?? ($idx + 1)) . ".jpg" : "-";
                $tagFile = $row->foto_tagging ? "foto_tagging/{$row->nomor_asset}_Tagging_" . ($row->id ?? ($idx + 1)) . ".jpg" : "-";

                $excelRows[] = [
                    '<style border="thin">' . ($row->time_stamps ? $row->time_stamps->format('Y-m-d H:i:s') : '') . '</style>',
                    '<style border="thin">' . $row->user . '</style>',
                    '<style border="thin">' . $row->nomor_asset . '</style>',
                    '<style border="thin">' . $row->deskripsi_asset . '</style>',
                    '<style border="thin">' . ($row->serial_number ?? '-') . '</style>',
                    '<style border="thin">' . $row->aktual_loc . '</style>',
                    '<style border="thin">' . $row->book_qty . '</style>',
                    '<style border="thin">' . $row->physic_qty . '</style>',
                    $varianceStyle,
                    '<style border="thin">' . $row->kelengkapan_tagging . '</style>',
                    '<style border="thin">' . $row->status . '</style>',
                    '<style border="thin">' . $row->kondisi . '</style>',
                    '<style border="thin">' . ($row->keterangan ?? '-') . '</style>',
                    '<style border="thin">' . $fisikFile . '</style>',
                    '<style border="thin">' . $tagFile . '</style>',
                ];
            }
        }

        $xlsx = SimpleXLSXGen::fromArray($excelRows);
        $xlsxContent = (string) $xlsx;

        // Jika hanya meminta file excel (.xlsx) saja
        if ($format === 'excel' || $format === 'xlsx') {
            return response($xlsxContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$excelBaseName}\"",
                'Content-Length' => strlen($xlsxContent),
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ]);
        }

        // 2. Bundling ZIP Archive (Excel + Folder Foto Fisik & Tagging Terpisah)
        $zipFileName = "Laporan_Opname_{$kategori}_{$timestampStr}.zip";
        $tempDir = storage_path('app/temp_export');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $zipFilePath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Masukkan file Excel ke root ZIP
            $zip->addFromString($excelBaseName, $xlsxContent);

            // Buat folder terpisah di dalam ZIP
            $zip->addEmptyDir('foto_fisik');
            $zip->addEmptyDir('foto_tagging');

            foreach ($data as $idx => $row) {
                $id = $row->id ?? ($idx + 1);
                $nomorAsset = preg_replace('/[^A-Za-z0-9_\-]/', '_', $row->nomor_asset);

                // 1. Foto Fisik
                $urlFisik = ($kategori === 'INTERNAL') ? $row->link_foto_fisik : $row->foto_fisik;
                if (!empty($urlFisik)) {
                    $fisikContent = $this->getImageBinary($urlFisik);
                    if ($fisikContent !== null) {
                        $zip->addFromString("foto_fisik/{$nomorAsset}_Fisik_{$id}.jpg", $fisikContent);
                    }
                }

                // 2. Foto Tagging
                $urlTag = ($kategori === 'INTERNAL') ? $row->link_tagging_asset : $row->foto_tagging;
                if (!empty($urlTag)) {
                    $tagContent = $this->getImageBinary($urlTag);
                    if ($tagContent !== null) {
                        $zip->addFromString("foto_tagging/{$nomorAsset}_Tagging_{$id}.jpg", $tagContent);
                    }
                }
            }

            $zip->close();

            return response()->download($zipFilePath, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ])->deleteFileAfterSend(true);
        }

        // Fallback jika ZipArchive gagal dibuka
        return response($xlsxContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$excelBaseName}\"",
            'Content-Length' => strlen($xlsxContent),
        ]);
    }

    /**
     * Dapatkan binary data gambar dari local disk atau URL remote.
     */
    protected function getImageBinary(string $urlOrPath): ?string
    {
        try {
            // Cek di disk lokal dahulu
            $localPath = $this->storageService->resolveLocalPath($urlOrPath);
            if ($localPath && file_exists($localPath)) {
                return file_get_contents($localPath);
            }

            // Jika URL remote http / https
            if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
                $response = Http::timeout(4)->get($urlOrPath);
                if ($response->successful()) {
                    return $response->body();
                }
            }

            // Jika Base64 data URI
            if (str_starts_with($urlOrPath, 'data:image')) {
                $parts = explode(',', $urlOrPath);
                return base64_decode($parts[1] ?? '');
            }
        } catch (Throwable $e) {
            // Abaikan kegagalan download satu gambar agar proses export zip tetap berjalan lancar
        }

        return null;
    }
}
