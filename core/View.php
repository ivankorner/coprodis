<?php

namespace App\Core;

class View
{
    private static array $sharedData = [];

    public static function share(string $key, $value): void
    {
        self::$sharedData[$key] = $value;
    }

    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data = array_merge(self::$sharedData, $data);

        $viewContent = self::getViewContent($view, $data);

        if ($layout === 'none') {
            echo $viewContent;
            return;
        }

        $layoutFile = VIEWS_PATH . "/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            $data['content'] = $viewContent;
            extract($data);
            require $layoutFile;
        } else {
            echo $viewContent;
        }
    }

    public static function renderPartial(string $view, array $data = []): void
    {
        echo self::getViewContent($view, $data);
    }

    private static function getViewContent(string $view, array $data): string
    {
        $viewPath = str_replace('.', '/', $view);
        $file = VIEWS_PATH . "/{$viewPath}.php";

        if (!file_exists($file)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
