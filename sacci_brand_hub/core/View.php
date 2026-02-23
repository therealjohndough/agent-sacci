<?php

namespace Core;

/**
 * Simple view rendering helper.
 */
class View
{
    public static function render(string $view, array $data = [], string $layout = 'layout/main'): void
    {
        extract($data);
        $viewFile = dirname(__DIR__) . "/app/views/{$view}.php";
        $layoutFile = dirname(__DIR__) . "/app/views/{$layout}.php";
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View {$view} not found");
        }
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        include $layoutFile;
    }
}