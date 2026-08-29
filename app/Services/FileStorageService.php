<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Nama disk penyimpanan yang aktif (public / local).
     */
    protected string $disk;
    protected GoogleDriveService $googleDrive;

    public function __construct(GoogleDriveService $googleDrive)
    {
        $this->disk = config('filesystems.opname_disk', env('OPNAME_STORAGE_DISK', 'public'));
        $this->googleDrive = $googleDrive;
    }

    /**
     * Simpan file foto dari Form Request (UploadedFile) atau Base64 String ke Storage Laravel
     * dan lakukan sinkronisasi otomatis ke Google Drive jika terkonfigurasi.
     *
     * @param UploadedFile|string|null $fileData
     * @param string $prefix
     * @param string|null $type 'fisik' atau 'tagging'
     * @return string|null Public URL atau Path file tersimpan
     */
    public function storePhoto(UploadedFile|string|null $fileData, string $prefix = 'photo', ?string $type = null): ?string
    {
        if (empty($fileData)) {
            return null;
        }

        // Tentukan tipe foto (fisik / tagging) dari prefix jika tidak dispesifikasikan
        if (!$type) {
            $type = (stripos($prefix, 'tag') !== false) ? 'tagging' : 'fisik';
        }

        $savedPath = null;
        $fileName = null;

        // 1. Jika bertipe UploadedFile dari multipart form request
        if ($fileData instanceof UploadedFile) {
            $extension = $fileData->getClientOriginalExtension() ?: 'jpg';
            $fileName = $prefix . '_' . time() . '_' . Str::random(6) . '.' . $extension;
            $savedPath = $fileData->storeAs('opname-photos', $fileName, $this->disk);
        }
        // 2. Jika bertipe string (Base64 atau URL lama)
        elseif (is_string($fileData)) {
            if (str_starts_with($fileData, 'http://') || str_starts_with($fileData, 'https://') || str_starts_with($fileData, '/storage/')) {
                return $this->normalizeUrl($fileData);
            }

            if (str_contains($fileData, 'base64,')) {
                $parts = explode('base64,', $fileData);
                $decoded = base64_decode($parts[1] ?? '');
            } else {
                $decoded = base64_decode($fileData);
            }

            if ($decoded !== false && !empty($decoded)) {
                $fileName = $prefix . '_' . time() . '_' . Str::random(6) . '.jpg';
                $savedPath = 'opname-photos/' . $fileName;
                Storage::disk($this->disk)->put($savedPath, $decoded);
            }
        }

        if (!$savedPath || !$fileName) {
            return null;
        }

        // 3. Upload ke Google Drive Folder yang sesuai (Fisik / Tagging) jika terkonfigurasi
        $localFullPath = Storage::disk($this->disk)->path($savedPath);
        if (file_exists($localFullPath) && $this->googleDrive->isConfigured()) {
            $gdriveResult = $this->googleDrive->uploadFile($localFullPath, $fileName, $type);
            if ($gdriveResult && !empty($gdriveResult['web_view_link'])) {
                Log::info("File {$fileName} berhasil disinkronkan ke Google Drive ({$type}): {$gdriveResult['web_view_link']}");
            }
        }

        return $this->formatDiskUrl($savedPath);
    }

    /**
     * Format URL penyimpanan agar aman digunakan di localhost:port maupun live domain.
     */
    public function formatDiskUrl(string $path): string
    {
        if ($this->disk === 'public') {
            return '/storage/' . ltrim($path, '/');
        }

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Normalisasi URL agar tidak terpaku pada hostname/port tertentu saat local development.
     */
    public function normalizeUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        // Jika URL lokal lama seperti http://localhost/storage/ atau http://127.0.0.1/storage/
        if (preg_match('#^https?://[^/]+/storage/(.*)$#i', $url, $matches)) {
            return '/storage/' . $matches[1];
        }

        if (!str_starts_with($url, 'http') && !str_starts_with($url, 'data:') && !str_starts_with($url, '/')) {
            return '/storage/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Cari path file absolut di disk lokal dari URL atau path relatif database.
     */
    public function resolveLocalPath(?string $urlOrPath): ?string
    {
        if (empty($urlOrPath)) {
            return null;
        }

        // Bersihkan path relatif /storage/opname-photos/...
        $relativePath = preg_replace('#^/storage/#', '', $urlOrPath);
        $relativePath = ltrim($relativePath, '/');

        // Cek disk public
        $diskPath = Storage::disk($this->disk)->path($relativePath);
        if (file_exists($diskPath)) {
            return $diskPath;
        }

        // Cek langsung di public_path
        $publicDirect = public_path($urlOrPath);
        if (file_exists($publicDirect)) {
            return $publicDirect;
        }

        // Cek public_path('storage/' . $relativePath)
        $publicStorage = public_path('storage/' . $relativePath);
        if (file_exists($publicStorage)) {
            return $publicStorage;
        }

        return null;
    }
}
