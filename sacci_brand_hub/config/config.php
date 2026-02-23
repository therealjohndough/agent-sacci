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