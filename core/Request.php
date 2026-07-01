<?php

namespace App\Core;

class Request
{
    private array $params = [];
    private array $body = [];
    private array $queryParams = [];

    public function __construct()
    {
        $this->parseInput();
    }

    private function parseInput(): void
    {
        $this->queryParams = $_GET;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $this->body = $this->sanitizeArray($input);
        } elseif ($this->getMethod() === 'POST') {
            $this->body = $this->sanitizeArray($_POST);
        } else {
            parse_str(file_get_contents('php://input'), $this->body);
            $this->body = $this->sanitizeArray($this->body);
        }
    }

    private function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $this->sanitizeValue($value);
            }
        }
        return $sanitized;
    }

    private function sanitizeValue($value): string
    {
        if ($value === null) return '';
        $value = strip_tags((string)$value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        return trim($value);
    }

    public function getMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($method === 'POST' && isset($this->body['_method'])) {
            $method = strtoupper($this->body['_method']);
        }
        return $method;
    }

    public function getUri(): string
    {
        if (isset($_GET['url']) && $_GET['url'] !== '') {
            return '/' . ltrim($_GET['url'], '/');
        }
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        $script = $_SERVER['SCRIPT_NAME'];
        $dir = dirname($script);
        // Walk up directory levels until we find a matching prefix
        while ($dir !== '/' && $dir !== '.') {
            if (strpos($uri, $dir) === 0) {
                $uri = substr($uri, strlen($dir));
                break;
            }
            $dir = dirname($dir);
        }

        return '/' . trim($uri, '/');
    }

    public function get(string $key, $default = null)
    {
        return $this->body[$key] ?? $default;
    }

    public function query(string $key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->body;
    }

    public function only(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            if (isset($this->body[$key])) {
                $data[$key] = $this->body[$key];
            }
        }
        return $data;
    }

    public function has(string $key): bool
    {
        return isset($this->body[$key]);
    }

    public function file(string $key): ?array
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE
            ? $_FILES[$key]
            : null;
    }

    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function getIp(): string
    {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = explode(',', $_SERVER[$header])[0];
                return trim($ip);
            }
        }
        return '0.0.0.0';
    }

    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function csrfToken(): string
    {
        if (!Session::has('_csrf_token')) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return Session::get('_csrf_token');
    }

    public function validateCsrf(): bool
    {
        $token = $this->get('_csrf_token');
        return $token && hash_equals(Session::get('_csrf_token', ''), $token);
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }
}
