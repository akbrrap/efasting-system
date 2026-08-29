<?php

namespace App\Http\Controllers;

use App\Models\MasterAsset;
use App\Models\MasterAssetExternal;
use App\Models\RiwayatSo;
use App\Models\RiwayatSoExternal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard dengan statistik terhitung dari PHP backend.
     */
    public function index(Request $request): View|JsonResponse
    {
        $metrics = $this->calculateDashboardMetrics($request->input('month'), $request->input('year'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        }

        return view('dashboard.index', $metrics);
    }

    /**
     * API Endpoint untuk mengambil data statistik (JSON) untuk update grafik Chart.js.
     */
    public function apiStats(Request $request): JsonResponse
    {
        $metrics = $this->calculateDashboardMetrics($request->input('month'), $request->input('year'));

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Logika inti penggabungan data (Map) dan penghitungan agregat statistik opname.
     */
    private function calculateDashboardMetrics(?int $month = null, ?int $year = null): array
    {
        $targetDate = Carbon::now();
        if ($year && $month) {
            $targetDate = Carbon::createFromDate($year, $month, 1);
        }

        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        // Nama bulan dalam Bahasa Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanTahunLabel = $bulanIndo[$targetDate->month] . ' ' . $targetDate->year;

        // 1. Tarik Data Opname Internal Bulan Ini
        $dataInt = RiwayatSo::whereBetween('timestamp', [$startOfMonth, $endOfMonth])
            ->orderBy('timestamp', 'asc')
            ->get();

        // De-duplikasi aset internal (hanya hitung 1 record terbaru per nomor_asset)
        $uniqueIntMap = [];
        foreach ($dataInt as $item) {
            if (!empty($item->nomor_asset)) {
                $uniqueIntMap[$item->nomor_asset] = $item;
            }
        }
        $intValid = array_values($uniqueIntMap);

        // 2. Tarik Data Opname External Bulan Ini
        $dataExt = RiwayatSoExternal::whereBetween('time_stamps', [$startOfMonth, $endOfMonth])
            ->orderBy('time_stamps', 'asc')
            ->get();

        // De-duplikasi aset eksternal
        $uniqueExtMap = [];
        foreach ($dataExt as $item) {
            if (!empty($item->nomor_asset)) {
                $uniqueExtMap[$item->nomor_asset] = $item;
            }
        }
        $extValid = array_values($uniqueExtMap);

        // 3. Kalkulasi Metrik Tagging, Kondisi, dan Status Penggunaan
        $totalInternal = count($intValid);
        $totalExternal = count($extValid);

        $tagAda = 0;
        $tagTidak = 0;
        $konBaik = 0;
        $konRusak = 0;
        $statGuna = 0;
        $statSem = 0;
        $statPerm = 0;

        foreach ($intValid as $item) {
            // Tagging
            if ($item->tagging === 'Ada') {
                $tagAda++;
            } elseif ($item->tagging === 'Tidak Ada') {
                $tagTidak++;
            }

            // Kondisi
            if ($item->kondisi === 'Baik') {
                $konBaik++;
            } elseif ($item->kondisi === 'Rusak') {
                $konRusak++;
            }

            // Status Penggunaan
            if ($item->status_penggunaan === 'Digunakan') {
                $statGuna++;
            } elseif ($item->status_penggunaan === 'Idle Sementara') {
                $statSem++;
            } elseif ($item->status_penggunaan === 'Idle Permanen') {
                $statPerm++;
            }
        }

        foreach ($extValid as $item) {
            // Tagging
            if ($item->kelengkapan_tagging === 'Ada') {
                $tagAda++;
            } elseif ($item->kelengkapan_tagging === 'Tidak Ada') {
                $tagTidak++;
            }

            // Kondisi
            if ($item->kondisi === 'Baik') {
                $konBaik++;
            } elseif ($item->kondisi === 'Rusak') {
                $konRusak++;
            }

            // Status Penggunaan
            if ($item->status === 'Digunakan') {
                $statGuna++;
            } elseif ($item->status === 'Idle Sementara') {
                $statSem++;
            } elseif ($item->status === 'Idle Permanen') {
                $statPerm++;
            }
        }

        // Total Master Asset di Database
        $masterInternalCount = MasterAsset::count();
        $masterExternalCount = MasterAssetExternal::count();

        $percentInternal = $masterInternalCount > 0 ? round(($totalInternal / $masterInternalCount) * 100, 1) : 0;
        $percentExternal = $masterExternalCount > 0 ? round(($totalExternal / $masterExternalCount) * 100, 1) : 0;

        return [
            'bulanTahunLabel' => $bulanTahunLabel,
            'totalInternal' => $totalInternal,
            'totalExternal' => $totalExternal,
            'masterInternalCount' => $masterInternalCount,
            'masterExternalCount' => $masterExternalCount,
            'percentInternal' => $percentInternal,
            'percentExternal' => $percentExternal,
            'tagAda' => $tagAda,
            'tagTidak' => $tagTidak,
            'konBaik' => $konBaik,
            'konRusak' => $konRusak,
            'statGuna' => $statGuna,
            'statSem' => $statSem,
            'statPerm' => $statPerm,
        ];
    }
}
