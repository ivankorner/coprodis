<?php

namespace App\Core;

class Controller
{
    protected Request $request;

    public function __construct()
    {
        View::share('appName', APP_NAME);
        View::share('appUrl', APP_URL);
        View::share('currentUser', Session::userData());
        $req = new Request();
        View::share('csrf_token', $req->csrfToken());
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json($data, int $status = 200): void
    {
        View::json($data, $status);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function redirectWith(string $url, string $type, string $message): void
    {
        Session::setFlash($type, $message);
        $this->redirect($url);
    }

    protected function back(): void
    {
        $url = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        $this->redirect($url);
    }

    protected function backWith(string $type, string $message): void
    {
        Session::setFlash($type, $message);
        $this->back();
    }

    protected function authorize(string $role): void
    {
        if (Session::userRole() !== $role) {
            $this->redirectWith(APP_URL, 'error', 'No tienes permisos para acceder a esta sección.');
            exit;
        }
    }

    protected function authorizeAny(array $roles): void
    {
        if (!in_array(Session::userRole(), $roles)) {
            $this->redirectWith(APP_URL, 'error', 'No tienes permisos para acceder a esta sección.');
            exit;
        }
    }

    protected function requireAuth(): void
    {
        if (!Session::isAuthenticated()) {
            $this->redirect(APP_URL . '/login');
            exit;
        }
    }

    protected function requirePasswordChange(): void
    {
        if (Session::get('must_change_password', false)) {
            $this->redirect(APP_URL . '/change-password');
            exit;
        }
    }
}
