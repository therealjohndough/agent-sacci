<?php

namespace Core;

use Exception;

/**
 * Simple router for mapping URIs to controller actions.
 */
class Router
{
    private array $routes = [];
    private string $basePath = '';

    /**
     * Configure the URI base path that will be removed from incoming request URIs before route matching.
     *
     * Provide a path such as '/sacci_brand_hub' to make routes match relative to that prefix; pass an empty
     * string to disable base-path stripping.
     *
     * @param string $path The base path prefix to strip (leading slash recommended, trailing slash will be ignored).
     */
    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($path, '/');
    }

    /**
     * Register a route with HTTP method(s).
     */
    public function add(string $method, string $path, callable|array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    /**
     * Routes an incoming HTTP method/URI to a registered handler and returns the handler's result.
     *
     * The URI path is parsed and, if a base path is configured, that prefix is removed before route lookup.
     * If no route matches the resolved path a 404 status and "404 Not Found" are emitted and the method returns null.
     * When a handler is an array in the form [ControllerClass, 'action'], the controller class is instantiated and the action method is invoked.
     *
     * @param string $method HTTP method for the request (e.g., "GET", "POST").
     * @param string $uri Full request URI or path to dispatch.
     * @return mixed The value returned by the matched handler, or `null` if no route matched.
     * @throws Exception If a handler is an array referencing a controller class that does not exist.
     */
    public function dispatch(string $method, string $uri): mixed
    {
        $method = strtoupper($method);
        $rawPath = parse_url($uri, PHP_URL_PATH) ?? '/';
        // Strip the subfolder prefix so routes can be defined without it.
        if ($this->basePath !== '' && str_starts_with($rawPath, $this->basePath)) {
            $baseLen = strlen($this->basePath);
        $rawPath = parse_url($uri, PHP_URL_PATH) ?? '/';
        // Strip the subfolder prefix so routes can be defined without it.
        if (
            $this->basePath !== '' &&
            ($rawPath === $this->basePath || str_starts_with($rawPath, $this->basePath . '/'))
        ) {
            $rawPath = substr($rawPath, strlen($this->basePath));
        }
        $path = $this->normalizePath($rawPath ?: '/');
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