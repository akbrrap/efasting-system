<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpnameController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - eFasting System (Enterprise Laravel 11)
|--------------------------------------------------------------------------
*/

// Halaman Utama -> Redirect ke Login atau Dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Autentikasi Pengguna (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Sistem Inti yang Terautentikasi (Auth Protected)
Route::middleware('auth')->group(function () {
    // Logout & Session Info
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/api/me', [AuthController::class, 'user'])->name('api.me');

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'apiStats'])->name('api.dashboard.stats');

    // API Helper untuk Pencarian Barcode & Lokasi
    Route::get('/api/assets/search', [AssetController::class, 'apiSearch'])->name('api.assets.search');
    Route::get('/api/assets/locations', [AssetController::class, 'apiLocations'])->name('api.assets.locations');
    Route::get('/api/lokasi/search', [AssetController::class, 'apiSearchLocations'])->name('api.lokasi.search');

    // 2. Stock Opname Internal (ADMINISTRATOR & INTERNAL)
    Route::middleware('role:ADMINISTRATOR,INTERNAL')->group(function () {
        Route::get('/opname/internal', [OpnameController::class, 'internal'])->name('opname.internal');
        Route::post('/opname/internal', [OpnameController::class, 'storeInternal'])->name('opname.internal.store');
    });

    // 3. Stock Opname External (ADMINISTRATOR & EKSTERNAL)
    Route::middleware('role:ADMINISTRATOR,EKSTERNAL')->group(function () {
        Route::get('/opname/external', [OpnameController::class, 'external'])->name('opname.external');
        Route::post('/opname/external', [OpnameController::class, 'storeExternal'])->name('opname.external.store');
    });

    // 4. Menu Khusus Administrator & Internal
    Route::middleware('role:ADMINISTRATOR,INTERNAL')->group(function () {
        // Audit Trail (Bisa diakses Admin & Auditor Internal)
        Route::get('/audit-trail', [OpnameController::class, 'auditTrail'])->name('opname.audit_trail');
        Route::get('/api/audit-trail/history', [OpnameController::class, 'apiAssetHistory'])->name('api.audit.history');

        // Reports & Export
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // 5. Menu Khusus Administrator Saja
    Route::middleware('role:ADMINISTRATOR')->group(function () {
        // Master Assets & Single Operations
        Route::get('/assets', [AssetController::class, 'index'])->name('asset.index');
        Route::get('/assets/create', [AssetController::class, 'create'])->name('asset.create');
        Route::post('/assets', [AssetController::class, 'store'])->name('asset.store');
        Route::get('/assets/adjustment', [AssetController::class, 'adjustment'])->name('asset.adjustment');
        Route::post('/assets/adjustment', [AssetController::class, 'update'])->name('asset.adjustment.update');
        Route::get('/assets/retirement', [AssetController::class, 'retirement'])->name('asset.retirement');
        Route::post('/assets/retirement', [AssetController::class, 'processRetirement'])->name('asset.retirement.store');

        // Bulk Excel / CSV Operations (Backend Processing)
        Route::post('/assets/mass-addition', [AssetController::class, 'uploadMassAddition'])->name('asset.mass_addition');
        Route::post('/assets/mass-retirement', [AssetController::class, 'uploadMassRetirement'])->name('asset.mass_retirement');
        Route::post('/assets/mass-adjustment', [AssetController::class, 'uploadMassAdjustment'])->name('asset.mass_adjustment');
        Route::get('/assets/template/{type}', [AssetController::class, 'downloadTemplate'])->name('asset.template');
    });
});
