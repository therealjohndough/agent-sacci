<?php

namespace Core;

use Exception;

/**
 * Simple router for mapping URIs to controller actions.
 */
class Router
{
    private array $routes = [];

    /**
     * Register a route with HTTP method(s).
     */
    public function add(string $method, string $path, callable|array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    /**
     * Dispatch the request to the appropriate handler.
     */
    public function dispatch(string $method, string $uri): mixed
    {
        $method = strtoupper($method);
        $path = $this->normalizePath(parse_url($uri, PHP_URL_PATH) ?? '/');
        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo '404 Not Found';
            return null;
        }
        // If handler is array [class, method], instantiate and call
        if (is_array($handler)) {
            [$controllerClass, $action] = $handler;
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller {$controllerClass} not found");
            }
            $controller = new $controllerClass();
            return $controller->$action();
        }
        return call_user_func($handler);
    }

    private function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }
}