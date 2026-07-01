<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $roles = [])
    {
        $this->allowedRoles = $roles;
    }

    public function handle(Request $request): bool
    {
        $userRole = Session::userRole();

        if (empty($this->allowedRoles)) {
            return true;
        }

        if (!in_array($userRole, $this->allowedRoles)) {
            Session::setFlash('error', 'No tienes permisos para acceder a esta sección.');
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }

        return true;
    }
}
