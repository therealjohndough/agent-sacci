<?php

namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Csrf;

class AuthController extends BaseController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }
        $this->render('auth/login', [
            'csrf' => Csrf::token(),
        ]);
    }

    /**
     * Process a login POST: validate CSRF token, authenticate credentials, and route accordingly.
     *
     * If the CSRF token is invalid, execution is terminated with the message "Invalid CSRF token".
     * If authentication fails, re-renders the 'auth/login' view with an "Invalid credentials" error and a fresh CSRF token.
     * On successful authentication, logs the user in and redirects to '/app'.
     */
    private function handleLogin(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $token = $_POST['_csrf'] ?? '';
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }
        $user = User::findByEmail($email);
        if (!$user || !User::verifyPassword($user, $password)) {
            $this->render('auth/login', [
                'error' => 'Invalid credentials',
                'csrf' => Csrf::token(),
            ]);
            return;
        }
        Auth::login($user['id']);
        $this->redirect('/app');
    }

    /**
     * Logs out the current user and redirects to the login page.
     */
    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}