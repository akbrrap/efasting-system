<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSo;
use App\Models\RiwayatSoExternal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Shuchkin\SimpleXLSXGen;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman Export Laporan Audit Opname.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Export Data Laporan ke Microsoft Excel (.xlsx) atau JSON.
     */
    public function export(Request $request): JsonResponse|Response
    {
        $validated = $request->validate([
            'kategori' => 'required|in:INTERNAL,EXTERNAL',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'nullable|string|in:json,excel,xlsx,csv',
        ]);

        $kategori = strtoupper($validated['kategori']);
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $format = $validated['format'] ?? 'xlsx';

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

        // Generate Microsoft Excel (.xlsx)
        $excelRows = [];
        $periode = ($startDate && $endDate) ? "{$startDate} s/d {$endDate}" : "Semua Periode";

        if ($kategori === 'INTERNAL') {
            $fileName = "Laporan_Opname_Internal_" . date('Ymd_His') . ".xlsx";

            // Row 1: Title
            $excelRows[] = [
                '<style bgcolor="#004B87" color="#FFFFFF" font-size="12"><b>LAPORAN STOCK OPNAME ASSET INTERNAL - eFASTING</b></style>',
                '', '', '', '', '', '', '', '', '', '', '', ''
            ];
            // Row 2: Metadata Info
            $excelRows[] = [
                "<style bgcolor=\"#E6F0FA\" color=\"#004B87\"><b>Periode:</b> {$periode} | <b>Total Data:</b> {$data->count()} Record | <b>Tanggal Unduh:</b> " . date('d/m/Y H:i') . "</style>",
                '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            // Row 3: Headers
            $headers = [
                "TIMESTAMP", "USER / PETUGAS", "NOMOR_ASET", "DESKRIPSI_ASET",
                "SERIAL_NUMBER", "QTY_BUKU", "QTY_FISIK", "SELISIH",
                "TAGGING", "STATUS_PENGGUNAAN", "KONDISI", "LOKASI",
                "LINK_FOTO_FISIK", "LINK_TAGGING_ASSET"
            ];
            $headerFormatted = [];
            foreach ($headers as $h) {
                $headerFormatted[] = '<style bgcolor="#004B87" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $excelRows[] = $headerFormatted;

            // Data Rows
            foreach ($data as $row) {
                $selisihStyle = ($row->selisih != 0) ? '<style border="thin" color="#C0392B"><b>' . $row->selisih . '</b></style>' : '<style border="thin">' . $row->selisih . '</style>';
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
                    '<style border="thin">' . ($row->link_foto_fisik ?? '') . '</style>',
                    '<style border="thin">' . ($row->link_tagging_asset ?? '') . '</style>',
                ];
            }

        } else {
            $fileName = "Laporan_Opname_External_" . date('Ymd_His') . ".xlsx";

            // Row 1: Title
            $excelRows[] = [
                '<style bgcolor="#004B87" color="#FFFFFF" font-size="12"><b>LAPORAN STOCK OPNAME ASSET EXTERNAL - eFASTING</b></style>',
                '', '', '', '', '', '', '', '', '', '', '', ''
            ];
            // Row 2: Metadata Info
            $excelRows[] = [
                "<style bgcolor=\"#E6F0FA\" color=\"#004B87\"><b>Periode:</b> {$periode} | <b>Total Data:</b> {$data->count()} Record | <b>Tanggal Unduh:</b> " . date('d/m/Y H:i') . "</style>",
                '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            // Row 3: Headers
            $headers = [
                "TIME_STAMPS", "USER / PETUGAS", "NOMOR_ASET", "DESKRIPSI_ASET",
                "SERIAL_NUMBER", "AKTUAL_LOC", "BOOK_QTY", "PHYSIC_QTY",
                "VARIANCE", "TAGGING", "STATUS", "KONDISI",
                "KETERANGAN", "FOTO_FISIK", "FOTO_TAGGING"
            ];
            $headerFormatted = [];
            foreach ($headers as $h) {
                $headerFormatted[] = '<style bgcolor="#004B87" color="#FFFFFF" border="thin"><b>' . $h . '</b></style>';
            }
            $excelRows[] = $headerFormatted;

            // Data Rows
            foreach ($data as $row) {
                $varianceStyle = ($row->variance != 0) ? '<style border="thin" color="#C0392B"><b>' . $row->variance . '</b></style>' : '<style border="thin">' . $row->variance . '</style>';
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
                    '<style border="thin">' . ($row->foto_fisik ?? '') . '</style>',
                    '<style border="thin">' . ($row->foto_tagging ?? '') . '</style>',
                ];
            }
        }

        $xlsx = SimpleXLSXGen::fromArray($excelRows);
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
}
