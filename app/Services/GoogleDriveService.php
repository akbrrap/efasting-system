<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleDriveService
{
    protected string $folderFisikId;
    protected string $folderTaggingId;
    protected ?GoogleClient $client = null;
    protected ?GoogleDrive $driveService = null;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->folderFisikId = config('services.google_drive.folder_fisik_id', '1bbK_kpW2QGm8D9u740mLtpyunJbSNFxS');
        $this->folderTaggingId = config('services.google_drive.folder_tagging_id', '15Dp7vco1OTTWcQzogFJpcXzVSu7gTDT6');

        $this->initClient();
    }

    /**
     * Inisialisasi Google Client jika service account JSON atau credentials tersedia.
     */
    protected function initClient(): void
    {
        try {
            $jsonPath = config('services.google_drive.service_account_json');
            $jsonKey = config('services.google_drive.service_account_key');

            if ($jsonKey) {
                $client = new GoogleClient();
                $client->setAuthConfig(json_decode($jsonKey, true));
                $client->addScope(GoogleDrive::DRIVE);
                $this->client = $client;
                $this->driveService = new GoogleDrive($client);
                $this->isConfigured = true;
            } elseif ($jsonPath && file_exists($jsonPath)) {
                $client = new GoogleClient();
                $client->setAuthConfig($jsonPath);
                $client->addScope(GoogleDrive::DRIVE);
                $this->client = $client;
                $this->driveService = new GoogleDrive($client);
                $this->isConfigured = true;
            }
        } catch (Throwable $e) {
            Log::warning('GoogleDriveService: Gagal inisialisasi Google Client: ' . $e->getMessage());
            $this->isConfigured = false;
        }
    }

    /**
     * Cek apakah Google Drive API siap digunakan.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured && $this->driveService !== null;
    }

    /**
     * Dapatkan Folder ID berdasarkan tipe foto.
     */
    public function getFolderId(string $type = 'fisik'): string
    {
        $type = strtolower($type);
        if (str_contains($type, 'tag')) {
            return $this->folderTaggingId;
        }

        return $this->folderFisikId;
    }

    /**
     * Upload file ke folder Google Drive yang sesuai.
     *
     * @param string $localFilePath Path fisik file lokal
     * @param string $fileName Nama file yang akan ditampilkan di Google Drive
     * @param string $type 'fisik' atau 'tagging'
     * @return array|null Info file Google Drive
     */
    public function uploadFile(string $localFilePath, string $fileName, string $type = 'fisik'): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            if (!file_exists($localFilePath)) {
                return null;
            }

            $folderId = $this->getFolderId($type);
            $mimeType = mime_content_type($localFilePath) ?: 'image/jpeg';

            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [$folderId],
            ]);

            $content = file_get_contents($localFilePath);

            $file = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink, webContentLink',
            ]);

            // Set permission agar file bisa dilihat oleh publik jika didukung
            try {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'reader',
                ]);
                $this->driveService->permissions->create($file->id, $permission);
            } catch (Throwable $permError) {
                // Abaikan jika service account tidak punya akses edit permission
            }

            $fileId = $file->id;
            $webViewLink = $file->webViewLink ?? "https://drive.google.com/file/d/{$fileId}/view";
            $directLink = "https://drive.google.com/thumbnail?id={$fileId}&sz=w1000";

            return [
                'file_id' => $fileId,
                'name' => $fileName,
                'folder_id' => $folderId,
                'web_view_link' => $webViewLink,
                'direct_link' => $directLink,
            ];
        } catch (Throwable $e) {
            Log::error('GoogleDriveService: Gagal upload file ke Google Drive: ' . $e->getMessage());
            return null;
        }
    }
}
