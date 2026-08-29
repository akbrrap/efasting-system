<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpnameExternalRequest;
use App\Http\Requests\StoreOpnameInternalRequest;
use App\Models\RiwayatSo;
use App\Models\RiwayatSoExternal;
use App\Services\FileStorageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpnameController extends Controller
{
    protected FileStorageService $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Tampilkan formulir Stock Opname Internal Assets.
     */
    public function internal(): View
    {
        return view('opname.internal');
    }

    /**
     * Tampilkan formulir Stock Opname External Assets.
     */
    public function external(): View
    {
        return view('opname.external');
    }

    /**
     * Simpan hasil Stock Opname Internal Asset via Form Request & FileStorageService.
     */
    public function storeInternal(StoreOpnameInternalRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $qtyBuku = (int) $validated['qty_buku'];
        $qtyFisik = (int) $validated['qty_fisik'];
        $selisih = $qtyFisik - $qtyBuku;

        // Upload foto via FileStorageService (Laravel Storage / Supabase S3)
        $linkFotoFisik = $this->storageService->storePhoto(
            $request->file('foto_fisik') ?? $request->input('foto_fisik'),
            $validated['nomor_asset'] . '_Fisik'
        );

        $linkFotoTagging = $this->storageService->storePhoto(
            $request->file('foto_tagging') ?? $request->input('foto_tagging'),
            $validated['nomor_asset'] . '_Tag'
        );

        $riwayat = RiwayatSo::create([
            'timestamp' => Carbon::now(),
            'user' => auth()->user()->nama_karyawan ?? 'Petugas Internal',
            'nomor_asset' => $validated['nomor_asset'],
            'deskripsi_asset' => $validated['deskripsi_asset'],
            'serial_number' => $validated['serial_number'] ?? '-',
            'qty_buku' => $qtyBuku,
            'qty_fisik' => $qtyFisik,
            'selisih' => $selisih,
            'tagging' => $validated['tagging'],
            'status_penggunaan' => $validated['status_penggunaan'],
            'kondisi' => $validated['kondisi'],
            'lokasi' => $validated['lokasi'],
            'link_foto_fisik' => $linkFotoFisik,
            'link_tagging_asset' => $linkFotoTagging,
        ]);

        $msg = "Data Stock Opname Internal aset {$validated['nomor_asset']} berhasil disimpan.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => $msg,
                'data' => $riwayat,
            ]);
        }

        return redirect()->route('opname.internal')->with('success', $msg);
    }

    /**
     * Simpan hasil Stock Opname External Asset via Form Request & FileStorageService.
     */
    public function storeExternal(StoreOpnameExternalRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $bookQty = (int) $validated['book_qty'];
        $physicQty = (int) $validated['physic_qty'];
        $variance = $physicQty - $bookQty;

        // Upload foto via FileStorageService
        $linkFotoFisik = $this->storageService->storePhoto(
            $request->file('foto_fisik') ?? $request->input('foto_fisik'),
            $validated['nomor_asset'] . '_Fisik_Ext'
        );

        $linkFotoTagging = $this->storageService->storePhoto(
            $request->file('foto_tagging') ?? $request->input('foto_tagging'),
            $validated['nomor_asset'] . '_Tag_Ext'
        );

        $riwayat = RiwayatSoExternal::create([
            'time_stamps' => Carbon::now(),
            'user' => auth()->user()->nama_karyawan ?? 'Petugas Eksternal',
            'nomor_asset' => $validated['nomor_asset'],
            'deskripsi_asset' => $validated['deskripsi_asset'],
            'serial_number' => $validated['serial_number'] ?? '-',
            'aktual_loc' => $validated['aktual_loc'],
            'book_qty' => $bookQty,
            'physic_qty' => $physicQty,
            'variance' => $variance,
            'kelengkapan_tagging' => $validated['kelengkapan_tagging'],
            'status' => $validated['status'],
            'kondisi' => $validated['kondisi'],
            'keterangan' => $validated['keterangan'] ?? '-',
            'foto_fisik' => $linkFotoFisik,
            'foto_tagging' => $linkFotoTagging,
        ]);

        $msg = "Data Stock Opname External aset {$validated['nomor_asset']} berhasil disimpan.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => $msg,
                'data' => $riwayat,
            ]);
        }

        return redirect()->route('opname.external')->with('success', $msg);
    }

    /**
     * Tampilkan halaman Audit Trail.
     */
    public function auditTrail(): View
    {
        return view('opname.audit_trail');
    }

    /**
     * API Riwayat Jejak Aset (Internal & External).
     */
    public function apiAssetHistory(Request $request): JsonResponse
    {
        $nomorAsset = trim($request->input('nomor_asset', ''));

        if (empty($nomorAsset)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Aset wajib diisi.',
            ], 422);
        }

        $resInt = RiwayatSo::where('nomor_asset', $nomorAsset)->get();
        $resExt = RiwayatSoExternal::where('nomor_asset', $nomorAsset)->get();

        $history = [];

        foreach ($resInt as $item) {
            $history[] = [
                'id' => $item->id,
                'tanggalView' => $item->timestamp ? $item->timestamp->translatedFormat('d F Y, H:i') : '-',
                'tanggalSort' => $item->timestamp ? $item->timestamp->timestamp : 0,
                'petugas' => $item->user ?? '-',
                'lokasi' => $item->lokasi ?? '-',
                'qtyFisik' => $item->qty_fisik ?? 0,
                'kondisi' => $item->kondisi ?? '-',
                'status' => $item->status_penggunaan ?? '-',
                'fotoFisik' => $this->storageService->normalizeUrl($item->link_foto_fisik),
                'fotoTagging' => $this->storageService->normalizeUrl($item->link_tagging_asset),
                'tipe' => 'Internal',
            ];
        }

        foreach ($resExt as $item) {
            $history[] = [
                'id' => $item->id,
                'tanggalView' => $item->time_stamps ? $item->time_stamps->translatedFormat('d F Y, H:i') : '-',
                'tanggalSort' => $item->time_stamps ? $item->time_stamps->timestamp : 0,
                'petugas' => $item->user ?? '-',
                'lokasi' => ($item->aktual_loc ?? '-') . ' (Cat: ' . ($item->keterangan ?? '-') . ')',
                'qtyFisik' => $item->physic_qty ?? 0,
                'kondisi' => $item->kondisi ?? '-',
                'status' => $item->status ?? '-',
                'fotoFisik' => $this->storageService->normalizeUrl($item->foto_fisik),
                'fotoTagging' => $this->storageService->normalizeUrl($item->foto_tagging),
                'tipe' => 'Eksternal',
            ];
        }

        usort($history, function ($a, $b) {
            return $b['tanggalSort'] <=> $a['tanggalSort'];
        });

        return response()->json([
            'success' => true,
            'nomor_asset' => $nomorAsset,
            'total' => count($history),
            'data' => $history,
        ]);
    }
}
