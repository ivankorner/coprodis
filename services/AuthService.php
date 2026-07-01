<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\SecurityHelper;

class AuthService
{
    public static function authenticate(string $email, string $password): array
    {
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT u.*, r.nombre as rol_nombre, r.slug as rol_slug
             FROM users u
             JOIN roles r ON u.rol_id = r.id
             WHERE u.email = :email AND u.deleted_at IS NULL",
            ['email' => $email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        // Check if blocked
        if ($user->estado === 'bloqueado' && $user->bloqueado_hasta) {
            if (strtotime($user->bloqueado_hasta) > time()) {
                $restante = ceil((strtotime($user->bloqueado_hasta) - time()) / 60);
                return ['success' => false, 'message' => "Cuenta bloqueada. Intenta nuevamente en {$restante} minutos."];
            }
            // Unblock
            $db->update('users', [
                'estado' => 'activo',
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ], 'id = :id', ['id' => $user->id]);
        }

        // Check if inactive
        if ($user->estado === 'inactivo') {
            return ['success' => false, 'message' => 'Tu cuenta está desactivada. Contacta al administrador.'];
        }

        // Verify password
        if (!SecurityHelper::verifyPassword($password, $user->password)) {
            // Increment failed attempts
            $attempts = (int)$user->intentos_fallidos + 1;
            if ($attempts >= 5) {
                $bloqueadoHasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $db->update('users', [
                    'intentos_fallidos' => $attempts,
                    'estado' => 'bloqueado',
                    'bloqueado_hasta' => $bloqueadoHasta,
                ], 'id = :id', ['id' => $user->id]);
                return ['success' => false, 'message' => 'Cuenta bloqueada por 15 minutos por múltiples intentos fallidos.'];
            }

            $db->update('users', ['intentos_fallidos' => $attempts], 'id = :id', ['id' => $user->id]);
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        // Successful login
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }

        $db->update('users', [
            'ultimo_acceso' => date('Y-m-d H:i:s'),
            'ip_acceso' => trim($ip),
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ], 'id = :id', ['id' => $user->id]);

        \App\Core\Session::set('user_id', $user->id);
        \App\Core\Session::set('user_role', $user->rol_slug);
        \App\Core\Session::set('user_data', $user);
        \App\Core\Session::set('last_activity', time());
        \App\Core\Session::set('must_change_password', (bool)$user->password_temporal);

        \App\Services\AuditService::register('inicio_sesion', 'auth', "Inicio de sesión: {$user->email}", $user->id);

        return ['success' => true, 'must_change_password' => (bool)$user->password_temporal];
    }

    public static function logout(): void
    {
        $userId = \App\Core\Session::userId();
        AuditService::register('cierre_sesion', 'auth', 'Cierre de sesión', $userId);
        \App\Core\Session::destroy();
    }

    public static function sendPasswordResetLink(string $email): array
    {
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT * FROM users WHERE email = :email AND deleted_at IS NULL",
            ['email' => $email]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Si el correo existe, recibirás un enlace de recuperación.'];
        }

        $token = SecurityHelper::generateToken(32);
        $expiraEn = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->insert('password_resets', [
            'user_id' => $user->id,
            'token' => $token,
            'expira_en' => $expiraEn,
        ]);

        $sent = \App\Services\MailService::sendPasswordReset($email, $user->nombre, $token);

        AuditService::register('solicitud_recuperacion', 'auth', "Solicitud de recuperación: {$email}", $user->id);

        return ['success' => true, 'message' => 'Si el correo existe, recibirás un enlace de recuperación.'];
    }

    public static function resetPassword(string $token, string $password): array
    {
        $db = Database::getInstance();

        $reset = $db->fetch(
            "SELECT * FROM password_resets
             WHERE token = :token AND usado = FALSE AND expira_en >= NOW()",
            ['token' => $token]
        );

        if (!$reset) {
            return ['success' => false, 'message' => 'Token inválido o expirado.'];
        }

        $hashedPassword = SecurityHelper::hashPassword($password);
        $db->update('users', [
            'password' => $hashedPassword,
            'password_temporal' => 0,
            'password_changed_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $reset->user_id]);

        $db->update('password_resets', ['usado' => 1], 'id = :id', ['id' => $reset->id]);

        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $reset->user_id]);

        AuditService::register('recuperacion_password', 'auth', "Contraseña restablecida: {$user->email}", $user->id);

        return ['success' => true, 'message' => 'Contraseña restablecida exitosamente.'];
    }

    public static function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $db = Database::getInstance();

        $user = $db->fetch("SELECT * FROM users WHERE id = :id", ['id' => $userId]);

        if (!SecurityHelper::verifyPassword($currentPassword, $user->password)) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
        }

        $hashedPassword = SecurityHelper::hashPassword($newPassword);
        $db->update('users', [
            'password' => $hashedPassword,
            'password_temporal' => 0,
            'password_changed_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $userId]);

        \App\Core\Session::set('must_change_password', false);

        AuditService::register('cambio_password', 'auth', 'Cambio de contraseña', $userId);

        return ['success' => true, 'message' => 'Contraseña cambiada exitosamente.'];
    }
}
