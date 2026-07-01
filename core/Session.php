<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function isAuthenticated(): bool
    {
        return self::has('user_id');
    }

    public static function userId(): ?int
    {
        return self::get('user_id');
    }

    public static function userRole(): ?string
    {
        return self::get('user_role');
    }

    public static function userData(): ?object
    {
        return self::get('user_data');
    }

    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }

    public static function hasFlash(string $type): bool
    {
        return isset($_SESSION['_flash'][$type]);
    }

    public static function checkExpiration(): void
    {
        if (!self::isAuthenticated()) {
            return;
        }

        $lastActivity = self::get('last_activity', 0);
        if (time() - $lastActivity > SESSION_LIFETIME) {
            self::destroy();
            return;
        }

        self::set('last_activity', time());
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
