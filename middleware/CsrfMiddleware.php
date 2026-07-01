<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Core\Response;

class CsrfMiddleware
{
    public function handle(Request $request): bool
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            if (!$request->validateCsrf()) {
                if ($request->isAjax()) {
                    Response::error('Token CSRF inválido', 419);
                }
                Session::setFlash('error', 'Token de seguridad inválido. Intenta nuevamente.');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL));
                exit;
            }
        }
        return true;
    }
}
