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
        header('Location: /app');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}