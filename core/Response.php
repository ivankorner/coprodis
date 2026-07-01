<?php

namespace App\Core;

class Response
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, string $message = 'Operación exitosa'): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(string $message = 'Error en la operación', int $status = 400, $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        self::json($response, $status);
    }

    public static function download(string $filePath, string $fileName = null): void
    {
        if (!file_exists($filePath)) {
            self::error('Archivo no encontrado', 404);
        }

        $fileName = $fileName ?? basename($filePath);
        $fileSize = filesize($filePath);
        $fileType = mime_content_type($filePath);

        header('Content-Type: ' . $fileType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        readfile($filePath);
        exit;
    }

    public static function file(string $filePath): void
    {
        if (!file_exists($filePath)) {
            self::error('Archivo no encontrado', 404);
        }

        $fileType = mime_content_type($filePath);
        header('Content-Type: ' . $fileType);
        header('Cache-Control: public, max-age=31536000');
        readfile($filePath);
        exit;
    }
}
