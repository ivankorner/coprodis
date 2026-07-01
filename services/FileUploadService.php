<?php

namespace App\Services;

use App\Helpers\SecurityHelper;

class FileUploadService
{
    private array $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    private array $allowedDocs = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'];
    private array $allowedAll = [];
    private int $maxSize = 10485760; // 10MB

    public function __construct()
    {
        $this->allowedAll = array_merge($this->allowedImages, $this->allowedDocs);
    }

    public function upload(array $file, string $directory = 'files', array $allowedTypes = null): array
    {
        $allowedTypes = $allowedTypes ?? $this->allowedAll;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'message' => "Tipo de archivo no permitido: .{$extension}"];
        }

        if ($file['size'] > $this->maxSize) {
            $maxMB = $this->maxSize / 1048576;
            return ['success' => false, 'message' => "El archivo excede el tamaño máximo de {$maxMB}MB."];
        }

        $filename = SecurityHelper::sanitizeFilename(pathinfo($file['name'], PATHINFO_FILENAME));
        $uniqueName = uniqid() . '_' . $filename . '.' . $extension;

        $uploadDir = UPLOADS_PATH . '/' . $directory;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filepath = $uploadDir . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Error al guardar el archivo.'];
        }

        return [
            'success' => true,
            'original_name' => $file['name'],
            'filename' => $uniqueName,
            'path' => "uploads/{$directory}/{$uniqueName}",
            'type' => $file['type'],
            'size' => $file['size'],
            'extension' => $extension,
        ];
    }

    public function uploadImage(array $file, string $directory = 'images'): array
    {
        return $this->upload($file, $directory, $this->allowedImages);
    }

    public function uploadSignature(string $dataUrl, string $directory = 'signatures'): array
    {
        $dataUrl = str_replace('data:image/png;base64,', '', $dataUrl);
        $dataUrl = str_replace(' ', '+', $dataUrl);
        $imageData = base64_decode($dataUrl);

        if ($imageData === false) {
            return ['success' => false, 'message' => 'Datos de firma inválidos.'];
        }

        $filename = 'firma_' . uniqid() . '.png';
        $uploadDir = UPLOADS_PATH . '/' . $directory;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filepath = $uploadDir . '/' . $filename;
        file_put_contents($filepath, $imageData);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => "uploads/{$directory}/{$filename}",
        ];
    }

    public function delete(string $path): bool
    {
        $fullPath = BASE_PATH . '/' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedAll;
    }
}
