<?php

namespace Core;

/**
 * CSRF token generator and validator.
 */
class Csrf
{
    /**
     * Get or generate a CSRF token for current session.
     */
    public static function token(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /**
     * Validate the provided token.
     */
    public static function validate(string $token): bool
    {
        return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}