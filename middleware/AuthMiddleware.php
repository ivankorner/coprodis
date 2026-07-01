<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Session::isAuthenticated()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
        return true;
    }
}
