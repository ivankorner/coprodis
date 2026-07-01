<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\Validator;
use App\Helpers\SecurityHelper;
use App\Services\AuditService;

class ProfileController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT u.*, r.nombre as rol_nombre
             FROM users u JOIN roles r ON u.rol_id = r.id
             WHERE u.id = :id",
            ['id' => Session::userId()]
        );

        $this->view('profile.index', ['user' => $user]);
    }

    public function update(Request $request): void
    {
        $this->requireAuth();
        $userId = Session::userId();
        $data = $request->only(['apellido', 'nombre', 'telefono', 'localidad']);

        $db = Database::getInstance();
        $db->update('users', $data, 'id = :id', ['id' => $userId]);

        $this->redirectWith(APP_URL . '/perfil', 'success', 'Perfil actualizado exitosamente.');
    }

    public function changePassword(Request $request): void
    {
        $this->requireAuth();
        $userId = Session::userId();
        $currentPassword = $request->get('current_password');
        $newPassword = $request->get('password');
        $confirmPassword = $request->get('password_confirmation');

        if (!$currentPassword || !$newPassword) {
            $this->redirectWith(APP_URL . '/perfil', 'error', 'Todos los campos son obligatorios.');
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirectWith(APP_URL . '/perfil', 'error', 'Las contraseñas no coinciden.');
        }

        if (strlen($newPassword) < 8) {
            $this->redirectWith(APP_URL . '/perfil', 'error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $userId]);

        if (!SecurityHelper::verifyPassword($currentPassword, $user->password)) {
            $this->redirectWith(APP_URL . '/perfil', 'error', 'La contraseña actual es incorrecta.');
        }

        $db->update('users', [
            'password' => SecurityHelper::hashPassword($newPassword),
            'password_changed_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $userId]);

        AuditService::register('cambio_password', 'perfil', 'Cambio de contraseña desde perfil');

        $this->redirectWith(APP_URL . '/perfil', 'success', 'Contraseña cambiada exitosamente.');
    }
}
