<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class ThrottleMiddleware
{
    private string $key;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(string $key = 'default', int $maxAttempts = 5, int $decayMinutes = 15)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(Request $request): bool
    {
        $attempts = Session::get("_throttle_{$this->key}_attempts", 0);
        $firstAttempt = Session::get("_throttle_{$this->key}_time", 0);

        if ($attempts >= $this->maxAttempts) {
            $elapsed = time() - $firstAttempt;
            if ($elapsed < $this->decayMinutes * 60) {
                $remaining = ceil(($this->decayMinutes * 60 - $elapsed) / 60);
                Session::setFlash('error', "Demasiados intentos. Intenta nuevamente en {$remaining} minutos.");
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL));
                exit;
            }
            // Reset after decay
            Session::remove("_throttle_{$this->key}_attempts");
            Session::remove("_throttle_{$this->key}_time");
        }

        return true;
    }

    public static function increment(string $key = 'default'): void
    {
        $attempts = Session::get("_throttle_{$key}_attempts", 0);
        if ($attempts === 0) {
            Session::set("_throttle_{$key}_time", time());
        }
        Session::set("_throttle_{$key}_attempts", $attempts + 1);
    }

    public static function reset(string $key = 'default'): void
    {
        Session::remove("_throttle_{$key}_attempts");
        Session::remove("_throttle_{$key}_time");
    }
}
