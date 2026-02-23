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
            header('Location: /login');
            exit;
        }
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