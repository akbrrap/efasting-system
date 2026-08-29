<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleDriveService
{
    protected string $folderFisikId;
    protected string $folderTaggingId;
    protected ?string $webAppUrl;
    protected ?GoogleClient $client = null;
    protected ?GoogleDrive $driveService = null;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $this->folderFisikId = config('services.google_drive.folder_fisik_id', '1bbK_kpW2QGm8D9u740mLtpyunJbSNFxS');
        $this->folderTaggingId = config('services.google_drive.folder_tagging_id', '15Dp7vco1OTTWcQzogFJpcXzVSu7gTDT6');
        $this->webAppUrl = config('services.google_drive.webapp_url', env('GOOGLE_DRIVE_WEBAPP_URL', null));

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
            } elseif (!empty($this->webAppUrl)) {
                $this->isConfigured = true;
            }
        } catch (Throwable $e) {
            Log::warning('GoogleDriveService: Gagal inisialisasi Google Client: ' . $e->getMessage());
            $this->isConfigured = false;
        }
    }

    /**
     * Cek apakah integrasi Google Drive siap digunakan.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured || ($this->driveService !== null) || !empty($this->webAppUrl);
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
        if (!file_exists($localFilePath)) {
            return null;
        }

        $folderId = $this->getFolderId($type);
        $mimeType = mime_content_type($localFilePath) ?: 'image/jpeg';
        $content = file_get_contents($localFilePath);

        // 1. Metode Google Apps Script Web App (Jika URL Web App disediakan di .env)
        if (!empty($this->webAppUrl)) {
            try {
                $response = Http::timeout(15)->post($this->webAppUrl, [
                    'folder_id' => $folderId,
                    'file_name' => $fileName,
                    'file_data' => base64_encode($content),
                    'mime_type' => $mimeType,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    return [
                        'file_id' => $json['id'] ?? null,
                        'name' => $fileName,
                        'folder_id' => $folderId,
                        'web_view_link' => $json['url'] ?? $json['webViewLink'] ?? "https://drive.google.com/drive/folders/{$folderId}",
                    ];
                }
            } catch (Throwable $e) {
                Log::error('GoogleDriveService (WebApp): Gagal upload: ' . $e->getMessage());
            }
        }

        // 2. Metode Google Service Account API
        if ($this->driveService !== null) {
            try {
                $fileMetadata = new DriveFile([
                    'name' => $fileName,
                    'parents' => [$folderId],
                ]);

                $file = $this->driveService->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'fields' => 'id, name, webViewLink, webContentLink',
                ]);

                try {
                    $permission = new \Google\Service\Drive\Permission([
                        'type' => 'anyone',
                        'role' => 'reader',
                    ]);
                    $this->driveService->permissions->create($file->id, $permission);
                } catch (Throwable $permError) {
                    // Abaikan jika permission tidak didukung
                }

                $fileId = $file->id;
                $webViewLink = $file->webViewLink ?? "https://drive.google.com/file/d/{$fileId}/view";

                return [
                    'file_id' => $fileId,
                    'name' => $fileName,
                    'folder_id' => $folderId,
                    'web_view_link' => $webViewLink,
                ];
            } catch (Throwable $e) {
                Log::error('GoogleDriveService (ServiceAccount): Gagal upload: ' . $e->getMessage());
            }
        }

        return null;
    }
}
