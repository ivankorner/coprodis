<?php

namespace App\Core;

class App
{
    private Router $router;
    private Request $request;

    public function __construct()
    {
        $this->request = new Request();
        $this->router = new Router();
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $routes = BASE_PATH . '/config/routes.php';
        if (file_exists($routes)) {
            $router = $this->router;
            require $routes;
        }
    }

    public function run(): void
    {
        try {
            Session::checkExpiration();
            $this->router->dispatch($this->request);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Exception $e): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            ob_end_clean();
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => APP_DEBUG ? $e->getMessage() : 'Error interno del servidor'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (APP_DEBUG) {
            echo '<h1>Error</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        } else {
            http_response_code(500);
            $this->showErrorPage(500, 'Error interno del servidor');
        }
    }

    private function showErrorPage(int $code, string $message): void
    {
        $viewFile = VIEWS_PATH . "/errors/{$code}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<h1>{$code}</h1><p>{$message}</p>";
        }
    }
}
