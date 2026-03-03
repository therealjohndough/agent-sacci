<?php

/**
 * Configuration loader.
 *
 * This file reads environment variables from a .env file in the project root
 * and exposes them via the env() helper function. If an environment
 * variable is already defined in the PHP environment it will not be
 * overwritten.
 */

namespace Config;

/**
 * Load environment variables from the .env file.
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim(trim($value), "\"'");
        if (!getenv($name)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

/**
 * Retrieve an environment variable with an optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

/**
 * Resolve the application base path for subfolder deployments.
 */
function appBase(?string $scriptName = null): string
{
    if (\defined('APP_BASE')) {
        return APP_BASE;
    }

    $configured = env('APP_BASE');
    if (is_string($configured) && $configured !== '') {
        return '/' . trim($configured, '/');
    }

    $scriptName ??= $_SERVER['SCRIPT_NAME'] ?? '';
    $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return ($base === '' || $base === '.') ? '' : $base;
}

/**
 * Generate an in-app URL that respects the configured base path.
 */
function appUrl(string $path = '/'): string
{
    $normalizedPath = '/' . ltrim($path, '/');
    $base = appBase();

    if ($normalizedPath === '/') {
        return $base !== '' ? $base . '/' : '/';
    }

    return ($base !== '' ? $base : '') . $normalizedPath;
}

/**
 * Return the current request path without the application base prefix.
 */
function requestPath(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = appBase();

    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base)) ?: '/';
    }

    return '/' . trim($uri, '/');
}
