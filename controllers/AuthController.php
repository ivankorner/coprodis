<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): void
    {
        if (\App\Core\Session::isAuthenticated()) {
            $this->redirect(APP_URL . '/dashboard');
        }
        $this->view('auth.login', [], 'auth');
    }

    public function login(Request $request): void
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $remember = $request->get('remember');

        if (!$email || !$password) {
            $this->redirectWith(APP_URL . '/login', 'error', 'Todos los campos son obligatorios.');
        }

        // Throttle
        \App\Middleware\ThrottleMiddleware::increment('login_' . $email);

        $result = AuthService::authenticate($email, $password);

        if (!$result['success']) {
            $this->redirectWith(APP_URL . '/login', 'error', $result['message']);
        }

        \App\Middleware\ThrottleMiddleware::reset('login_' . $email);

        if ($result['must_change_password']) {
            $this->redirect(APP_URL . '/change-password');
        }

        $this->redirect(APP_URL . '/dashboard');
    }

    public function logout(Request $request): void
    {
        AuthService::logout();
        $this->redirect(APP_URL . '/login');
    }

    public function showForgotForm(Request $request): void
    {
        $this->view('auth.forgot', [], 'auth');
    }

    public function sendResetLink(Request $request): void
    {
        $email = $request->get('email');
        $result = AuthService::sendPasswordResetLink($email);
        $this->redirectWith(APP_URL . '/login', 'success', $result['message']);
    }

    public function showResetForm(Request $request): void
    {
        $token = $request->param('token');
        $this->view('auth.reset', ['token' => $token], 'auth');
    }

    public function resetPassword(Request $request): void
    {
        $token = $request->get('token');
        $password = $request->get('password');
        $passwordConfirmation = $request->get('password_confirmation');

        if (!$password || $password !== $passwordConfirmation) {
            $this->redirectWith(APP_URL . '/reset-password/' . $token, 'error', 'Las contraseñas no coinciden.');
        }

        if (strlen($password) < 8) {
            $this->redirectWith(APP_URL . '/reset-password/' . $token, 'error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        $result = AuthService::resetPassword($token, $password);

        if (!$result['success']) {
            $this->redirectWith(APP_URL . '/login', 'error', $result['message']);
        }

        $this->redirectWith(APP_URL . '/login', 'success', 'Contraseña restablecida exitosamente. Inicia sesión.');
    }

    public function showChangePasswordForm(Request $request): void
    {
        $this->view('auth.change-password', [], 'main');
    }

    public function changePassword(Request $request): void
    {
        $userId = \App\Core\Session::userId();
        $currentPassword = $request->get('current_password');
        $newPassword = $request->get('password');
        $confirmPassword = $request->get('password_confirmation');

        if (!$currentPassword || !$newPassword) {
            $this->redirectWith(APP_URL . '/change-password', 'error', 'Todos los campos son obligatorios.');
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirectWith(APP_URL . '/change-password', 'error', 'Las contraseñas no coinciden.');
        }

        if (strlen($newPassword) < 8) {
            $this->redirectWith(APP_URL . '/change-password', 'error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        $result = AuthService::changePassword($userId, $currentPassword, $newPassword);

        if (!$result['success']) {
            $this->redirectWith(APP_URL . '/change-password', 'error', $result['message']);
        }

        $this->redirectWith(APP_URL . '/dashboard', 'success', 'Contraseña cambiada exitosamente.');
    }
}
