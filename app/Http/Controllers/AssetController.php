<?php

namespace App\Http\Controllers;

use App\Http\Requests\MassAdditionRequest;
use App\Http\Requests\MassAdjustmentRequest;
use App\Http\Requests\MassRetirementRequest;
use App\Models\MasterAsset;
use App\Models\MasterAssetExternal;
use App\Models\MasterLokasiExternal;
use App\Models\RiwayatRetirement;
use App\Services\AssetBulkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetController extends Controller
{
    /**
     * Tampilkan daftar Master Asset (Internal & External).
     */
    public function index(Request $request): View|JsonResponse
    {
        $kategori = strtoupper($request->input('kategori', 'INTERNAL'));
        $search = trim($request->input('search', ''));
        $perPage = (int) $request->input('limit', 50);

        $query = ($kategori === 'EXTERNAL') ? MasterAssetExternal::query() : MasterAsset::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_asset', 'like', "%{$search}%")
                    ->orWhere('deskripsi_asset', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('nomor_asset', 'asc')->paginate($perPage);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'kategori' => $kategori,
                'data' => $assets->items(),
                'pagination' => [
                    'current_page' => $assets->currentPage(),
                    'last_page' => $assets->lastPage(),
                    'per_page' => $assets->perPage(),
                    'total' => $assets->total(),
                ],
            ]);
        }

        return view('asset.index', compact('assets', 'kategori', 'search'));
    }

    /**
     * Tampilkan formulir penambahan aset baru (Single Addition).
     */
    public function create(): View
    {
        return view('asset.create');
    }

    /**
     * Simpan aset baru ke dalam database master (Internal / External).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'kategori_db' => 'required|in:INTERNAL,EXTERNAL',
            'nomor_asset' => 'required|string|max:50',
            'deskripsi_asset' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'cost_center' => 'nullable|string|max:50',
            'qty_buku' => 'required|integer|min:1',
            'cap_date' => 'nullable|date',
            'nilai_perolehan' => 'nullable|numeric|min:0',
            'akumulasi_depresiasi' => 'nullable|numeric|min:0',
            'nbv' => 'nullable|numeric',
            'allocation' => 'nullable|string|max:100',
        ]);

        $kategori = strtoupper($validated['kategori_db']);
        $nomorAsset = trim($validated['nomor_asset']);

        $existsInInternal = MasterAsset::where('nomor_asset', $nomorAsset)->exists();
        $existsInExternal = MasterAssetExternal::where('nomor_asset', $nomorAsset)->exists();

        if ($existsInInternal || $existsInExternal) {
            $msg = "Nomor Aset {$nomorAsset} sudah terdaftar di sistem.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['nomor_asset' => $msg])->withInput();
        }

        $data = [
            'nomor_asset' => $nomorAsset,
            'deskripsi_asset' => $validated['deskripsi_asset'],
            'serial_number' => $validated['serial_number'] ?? '-',
            'cost_center' => $validated['cost_center'],
            'qty_buku' => $validated['qty_buku'],
            'cap_date' => $validated['cap_date'],
            'nilai_perolehan' => $validated['nilai_perolehan'] ?? 0,
            'akumulasi_depresiasi' => $validated['akumulasi_depresiasi'] ?? 0,
            'nbv' => $validated['nbv'] ?? 0,
            'allocation' => $validated['allocation'],
        ];

        if ($kategori === 'INTERNAL') {
            MasterAsset::create($data);
        } else {
            MasterAssetExternal::create($data);
        }

        $successMsg = "Aset {$nomorAsset} telah berhasil ditambahkan ke Master Data {$kategori}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return redirect()->route('asset.index')->with('success', $successMsg);
    }

    /**
     * Backend Processing: Upload Mass Addition via Excel/CSV.
     */
    public function uploadMassAddition(MassAdditionRequest $request, AssetBulkService $bulkService): JsonResponse|RedirectResponse
    {
        try {
            $result = $bulkService->importAddition($request->file('file'));
            $msg = "Berhasil menambahkan {$result['success_count']} aset baru ke database (Dilewati: {$result['skipped_count']}).";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg, 'data' => $result]);
            }

            return redirect()->route('asset.index')->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    /**
     * Tampilkan halaman Asset Adjustment.
     */
    public function adjustment(): View
    {
        return view('asset.adjustment');
    }

    /**
     * Update nilai finansial aset.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'kategori_db' => 'required|in:INTERNAL,EXTERNAL',
            'nomor_asset' => 'required|string|max:50',
            'nilai_perolehan' => 'required|numeric|min:0',
            'akumulasi_depresiasi' => 'required|numeric|min:0',
            'nbv' => 'required|numeric',
        ]);

        $kategori = strtoupper($validated['kategori_db']);
        $model = ($kategori === 'INTERNAL')
            ? MasterAsset::where('nomor_asset', $validated['nomor_asset'])->first()
            : MasterAssetExternal::where('nomor_asset', $validated['nomor_asset'])->first();

        if (!$model) {
            $msg = "Aset {$validated['nomor_asset']} tidak ditemukan di database {$kategori}.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->withErrors(['nomor_asset' => $msg]);
        }

        $model->update([
            'nilai_perolehan' => $validated['nilai_perolehan'],
            'akumulasi_depresiasi' => $validated['akumulasi_depresiasi'],
            'nbv' => $validated['nbv'],
        ]);

        $msg = "Aset {$validated['nomor_asset']} berhasil di-update.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('asset.adjustment')->with('success', $msg);
    }

    /**
     * Backend Processing: Upload Mass Adjustment via Excel/CSV.
     */
    public function uploadMassAdjustment(MassAdjustmentRequest $request, AssetBulkService $bulkService): JsonResponse|RedirectResponse
    {
        try {
            $result = $bulkService->importAdjustment($request->file('file'));
            $msg = "Berhasil mengupdate {$result['success_count']} nilai aset secara massal.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg, 'data' => $result]);
            }

            return redirect()->route('asset.adjustment')->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    /**
     * Tampilkan halaman Fixed Asset Retirement.
     */
    public function retirement(): View
    {
        return view('asset.retirement');
    }

    /**
     * Eksekusi proses disposal aset (Single Retirement).
     */
    public function processRetirement(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'kategori_db' => 'required|in:INTERNAL,EXTERNAL',
            'nomor_asset' => 'required|string|max:50',
            'qty_disposal' => 'required|integer|min:1',
            'dokumen_sap' => 'required|string|max:100',
            'catatan' => 'required|string',
        ]);

        $kategori = strtoupper($validated['kategori_db']);
        $nomorAsset = trim($validated['nomor_asset']);
        $qtyDisposal = (int) $validated['qty_disposal'];

        $asset = ($kategori === 'INTERNAL')
            ? MasterAsset::where('nomor_asset', $nomorAsset)->first()
            : MasterAssetExternal::where('nomor_asset', $nomorAsset)->first();

        if (!$asset) {
            $msg = "Aset {$nomorAsset} tidak ditemukan di database {$kategori}.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->withErrors(['nomor_asset' => $msg]);
        }

        if ($qtyDisposal > $asset->qty_buku) {
            $msg = "Qty Disposal ({$qtyDisposal}) tidak boleh melebihi stok yang ada ({$asset->qty_buku}).";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['qty_disposal' => $msg]);
        }

        $rasio = $qtyDisposal / $asset->qty_buku;
        $nbvPotong = round($asset->nbv * $rasio, 2);
        $npPotong = round($asset->nilai_perolehan * $rasio, 2);
        $adPotong = round($asset->akumulasi_depresiasi * $rasio, 2);

        DB::transaction(function () use ($asset, $qtyDisposal, $nbvPotong, $npPotong, $adPotong, $kategori, $nomorAsset, $validated) {
            RiwayatRetirement::create([
                'petugas' => auth()->user()->nama_karyawan ?? 'System',
                'kategori_db' => $kategori,
                'nomor_asset' => $nomorAsset,
                'deskripsi_asset' => $asset->deskripsi_asset,
                'qty_disposal' => $qtyDisposal,
                'nbv_disposal' => $nbvPotong,
                'dokumen_sap' => $validated['dokumen_sap'],
                'catatan' => $validated['catatan'],
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
        });

        $msg = "Aset {$nomorAsset} berhasil di-disposal sebanyak {$qtyDisposal} unit dengan dokumen SAP {$validated['dokumen_sap']}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('asset.retirement')->with('success', $msg);
    }

    /**
     * Backend Processing: Upload Mass Retirement via Excel/CSV.
     */
    public function uploadMassRetirement(MassRetirementRequest $request, AssetBulkService $bulkService): JsonResponse|RedirectResponse
    {
        try {
            $result = $bulkService->importRetirement($request->file('file'), auth()->user()->nama_karyawan);
            $msg = "Berhasil memproses disposal masal untuk {$result['success_count']} aset.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg, 'data' => $result]);
            }

            return redirect()->route('asset.retirement')->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    /**
     * Download Template Spreadsheet Resmi Microsoft Excel (.xlsx).
     */
    public function downloadTemplate(string $type, Request $request, AssetBulkService $bulkService): \Symfony\Component\HttpFoundation\Response
    {
        $kategori = $request->input('kategori', 'INTERNAL');
        return $bulkService->generateTemplate($type, $kategori);
    }

    /**
     * API Pencarian Aset / Barcode Scanner Lookup.
     */
    public function apiSearch(Request $request): JsonResponse
    {
        $kategori = strtoupper($request->input('kategori', 'ALL'));
        $query = trim($request->input('query', ''));

        $results = [];

        if ($kategori === 'INTERNAL' || $kategori === 'ALL') {
            $intQuery = MasterAsset::query();
            if (!empty($query)) {
                $intQuery->where(function ($q) use ($query) {
                    $q->where('nomor_asset', 'like', "%{$query}%")
                        ->orWhere('deskripsi_asset', 'like', "%{$query}%")
                        ->orWhere('serial_number', 'like', "%{$query}%");
                });
            }
            $intAssets = $intQuery->limit(50)->get()->map(function ($item) {
                return [
                    'id' => $item->nomor_asset,
                    'desc' => $item->deskripsi_asset,
                    'sn' => $item->serial_number ?? '-',
                    'qty' => $item->qty_buku,
                    'cap_date' => $item->cap_date ? $item->cap_date->format('d/m/Y') : '-',
                    'nbv' => number_format($item->nbv, 0, ',', '.'),
                    'raw_nbv' => (float) $item->nbv,
                    'raw_np' => (float) $item->nilai_perolehan,
                    'raw_ad' => (float) $item->akumulasi_depresiasi,
                    'tipe' => 'INTERNAL',
                ];
            });
            $results = array_merge($results, $intAssets->toArray());
        }

        if ($kategori === 'EXTERNAL' || $kategori === 'ALL') {
            $extQuery = MasterAssetExternal::query();
            if (!empty($query)) {
                $extQuery->where(function ($q) use ($query) {
                    $q->where('nomor_asset', 'like', "%{$query}%")
                        ->orWhere('deskripsi_asset', 'like', "%{$query}%")
                        ->orWhere('serial_number', 'like', "%{$query}%");
                });
            }
            $extAssets = $extQuery->limit(50)->get()->map(function ($item) {
                return [
                    'id' => $item->nomor_asset,
                    'desc' => $item->deskripsi_asset,
                    'sn' => $item->serial_number ?? '-',
                    'qty' => $item->qty_buku,
                    'cap_date' => $item->cap_date ? $item->cap_date->format('d/m/Y') : '-',
                    'nbv' => number_format($item->nbv, 0, ',', '.'),
                    'raw_nbv' => (float) $item->nbv,
                    'raw_np' => (float) $item->nilai_perolehan,
                    'raw_ad' => (float) $item->akumulasi_depresiasi,
                    'tipe' => 'EXTERNAL',
                ];
            });
            $results = array_merge($results, $extAssets->toArray());
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * API Daftar Master Lokasi External.
     */
    public function apiLocations(): JsonResponse
    {
        $locations = MasterLokasiExternal::all()->map(function ($loc) {
            return [
                'id' => $loc->code_entity,
                'desc' => $loc->description,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }
}
