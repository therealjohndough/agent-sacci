<?php

namespace App\Controllers;

use Core\Auth;
use Core\Csrf;
use Core\View;

abstract class BaseController
{
    /**
     * Redirects unauthenticated users to the login page.
     *
     * If the current user is not authenticated, issues an HTTP redirect to '/login' and terminates execution.
     */
    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    /**
     * Redirects the client to a path within the application, honoring any APP_BASE subfolder.
     *
     * Sends an HTTP Location header for the computed URL and terminates execution.
     *
     * @param string $path The target path within the application; leading slash is optional.
     */
    protected function redirect(string $path): never
    {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }

    /**
     * Renders a template identified by the given view name using the provided data.
     *
     * @param string $view Path or name of the view template to render.
     * @param array $data Associative array of variables to make available to the view.
     */
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function csrfToken(): string
    {
        return Csrf::token();
    }
}