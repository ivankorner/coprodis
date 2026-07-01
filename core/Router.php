<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupMiddleware = [];
    private array $globalMiddleware = [];

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousMiddleware = $this->groupMiddleware;
        if (isset($attributes['middleware'])) {
            $this->groupMiddleware = array_merge($this->groupMiddleware, (array)$attributes['middleware']);
        }
        call_user_func($callback, $this);
        $this->groupMiddleware = $previousMiddleware;
    }

    public function addGlobalMiddleware(string $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    private function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        $path = '/' . trim($path, '/');
        $path = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => array_merge($this->globalMiddleware, $this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = '/' . trim($request->getUri(), '/');

        foreach ($this->routes as $route) {
            $pattern = '#^' . $route['path'] . '$#';
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                foreach ($route['middleware'] as $middleware) {
                    if (is_string($middleware)) {
                        $instance = new $middleware();
                    } else {
                        $instance = $middleware;
                    }
                    $result = $instance->handle($request);
                    if ($result === false) {
                        return;
                    }
                }

                $this->executeHandler($route['handler'], $request);
                return;
            }
        }

        http_response_code(404);
        $viewFile = VIEWS_PATH . '/errors/404.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo '<h1>404 - Página no encontrada</h1>';
        }
    }

    private function executeHandler($handler, Request $request): void
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $method] = explode('@', $handler);
            $controller = 'App\\Controllers\\' . $controller;
            $controllerInstance = new $controller();
            $controllerInstance->$method($request);
        } elseif (is_array($handler)) {
            [$controller, $method] = $handler;
            $controllerInstance = new $controller();
            $controllerInstance->$method($request);
        } elseif (is_callable($handler)) {
            $handler($request);
        }
    }
}
