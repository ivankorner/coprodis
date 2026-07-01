<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function generatePassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $special = '@#$%&*!';

        $chars = $upper . $lower . $digits . $special;
        $password = '';

        // Ensure at least one of each type
        $password .= $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return str_shuffle($password);
    }

    public static function encrypt(string $data, string $key): string
    {
        $cipher = 'aes-256-gcm';
        $iv = random_bytes(openssl_cipher_iv_length($cipher));
        $tag = '';

        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($iv . $tag . $encrypted);
    }

    public static function decrypt(string $data, string $key): string|false
    {
        $cipher = 'aes-256-gcm';
        $decoded = base64_decode($data);

        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($decoded, 0, $ivLength);
        $tag = substr($decoded, $ivLength, 16);
        $encrypted = substr($decoded, $ivLength + 16);

        return openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^\w\s\.\-]/', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        $filename = preg_replace('/\.+/', '.', $filename);
        return strtolower(trim($filename));
    }

    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        return $masked . '@' . $domain;
    }
}
