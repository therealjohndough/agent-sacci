<?php

namespace App\Controllers;

use Core\Auth;
use Core\Csrf;
use Core\View;

abstract class BaseController
{
    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    /**
     * Redirect to a path within the application, respecting the subfolder base path.
     */
    protected function redirect(string $path): never
    {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }

    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function csrfToken(): string
    {
        return Csrf::token();
    }
}