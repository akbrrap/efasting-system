<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Nama disk penyimpanan yang aktif (public / supabase / s3).
     */
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.opname_disk', env('OPNAME_STORAGE_DISK', 'public'));
    }

    /**
     * Simpan file foto dari Form Request (UploadedFile) atau Base64 String ke Storage Laravel.
     *
     * @param UploadedFile|string|null $fileData
     * @param string $prefix
     * @return string|null Public URL atau Path file tersimpan
     */
    public function storePhoto(UploadedFile|string|null $fileData, string $prefix = 'photo'): ?string
    {
        if (empty($fileData)) {
            return null;
        }

        // 1. Jika bertipe UploadedFile dari multipart form request
        if ($fileData instanceof UploadedFile) {
            $extension = $fileData->getClientOriginalExtension() ?: 'jpg';
            $fileName = $prefix . '_' . time() . '_' . Str::random(6) . '.' . $extension;
            $path = $fileData->storeAs('opname-photos', $fileName, $this->disk);

            return $this->formatDiskUrl($path);
        }

        // 2. Jika bertipe string (Base64 atau URL lama)
        if (is_string($fileData)) {
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
                $path = 'opname-photos/' . $fileName;

                Storage::disk($this->disk)->put($path, $decoded);

                return $this->formatDiskUrl($path);
            }
        }

        return null;
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
}
