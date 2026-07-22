<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\Validator;
use App\Helpers\SecurityHelper;
use App\Services\AuditService;
use App\Services\FileUploadService;

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
        $uploadService = new FileUploadService();

        if ($request->hasFile('avatar')) {
            $result = $uploadService->uploadImage($request->file('avatar'), 'images/avatars');
            if ($result['success']) {
                $current = $db->fetch("SELECT avatar FROM users WHERE id = :id", ['id' => $userId]);
                if (!empty($current->avatar)) {
                    $uploadService->delete($current->avatar);
                }
                $data['avatar'] = $result['path'];
            } else {
                $this->redirectWith(APP_URL . '/perfil', 'error', $result['message']);
            }
        } elseif ($request->has('_remove_avatar')) {
            $current = $db->fetch("SELECT avatar FROM users WHERE id = :id", ['id' => $userId]);
            if (!empty($current->avatar)) {
                $uploadService->delete($current->avatar);
            }
            $data['avatar'] = null;
        }

        $db->update('users', $data, 'id = :id', ['id' => $userId]);

        $user = $db->fetch(
            "SELECT u.*, r.nombre as rol_nombre FROM users u JOIN roles r ON u.rol_id = r.id WHERE u.id = :id",
            ['id' => $userId]
        );
        Session::set('user_data', $user);

        AuditService::register('actualizar_perfil', 'perfil', 'Perfil actualizado', null, 'info', $data, 'user', $userId);

        $this->redirectWith(APP_URL . '/perfil', 'success', 'Perfil actualizado exitosamente.');
    }

    public function changePassword(Request $request): void
    {
        $this->requireAuth();
        $userId = Session::userId();
        $currentPassword = $request->getRaw('current_password');
        $newPassword = $request->getRaw('password');
        $confirmPassword = $request->getRaw('password_confirmation');

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

        AuditService::register('cambio_password', 'perfil', 'Cambio de contraseña desde perfil', null, 'warning', [], 'user', $userId);

        $this->redirectWith(APP_URL . '/perfil', 'success', 'Contraseña cambiada exitosamente.');
    }
}
