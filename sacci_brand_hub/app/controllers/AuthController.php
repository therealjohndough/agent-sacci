<?php

namespace App\Controllers;

use App\Models\User;
use Core\Auth;
use Core\Csrf;
use Core\Database;
use Core\RateLimiter;

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
        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';
        $token    = $_POST['_csrf']    ?? '';

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        // ---------------------------------------------------------------------------
        // Rate limiting — max 5 login attempts per IP per 15 minutes (900 seconds).
        // Record the attempt before credential verification so failed and successful
        // attempts both count toward the window.
        // ---------------------------------------------------------------------------
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $limiter = new RateLimiter(Database::getConnection());

        if (!$limiter->check($ip, 'login', 5, 900)) {
            http_response_code(429);
            $this->render('auth/login', [
                'error' => 'Too many login attempts. Please wait 15 minutes before trying again.',
                'csrf'  => Csrf::token(),
            ]);
            return;
        }

        $limiter->record($ip, 'login');

        // ---------------------------------------------------------------------------
        // Credential verification
        // ---------------------------------------------------------------------------
        $user = User::findByEmail($email);
        if (!$user || !User::verifyPassword($user, $password)) {
            $this->render('auth/login', [
                'error' => 'Invalid credentials',
                'csrf'  => Csrf::token(),
            ]);
            return;
        }

        // Successful login — clear the rate limit record for this IP so a
        // legitimate user is not penalised by earlier failed attempts.
        $limiter->reset($ip, 'login');

        Auth::login($user['id']);
        $this->redirect('/app');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
