<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Core\Router;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Core\Router
 *
 * Router public API (from core/Router.php):
 *   - add(string $method, string $path, callable|array $handler): void
 *   - dispatch(string $method, string $uri): mixed
 *
 * dispatch() behaviour:
 *   - Path match found  → instantiate controller, call action method, return result
 *   - Path not found    → http_response_code(404), echo '404 Not Found', return null
 *   - Handler is a callable → call_user_func($handler)
 *   - Handler is an array [$class, $action] → new $class()->$action()
 *
 * normalizePath() (private) strips and re-adds a leading slash, so '/login',
 * 'login', and '/login/' all resolve to the same bucket.
 *
 * Design notes:
 *   - We register lightweight anonymous class handlers (not the real controllers)
 *     to avoid loading view files, PDO, etc.
 *   - http_response_code() and echo are real PHP built-ins; we capture output
 *     via output buffering and test the response code via http_response_code().
 */
class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        // Reset any response code set by a previous test.
        http_response_code(200);
    }

    // ── Helper: anonymous controller stub ────────────────────────────────────

    /**
     * Registers a minimal anonymous-class handler on the router so we can
     * test dispatch() without pulling in real controllers (which need DB, views,
     * session, etc.).
     *
     * Returns the controller class name so tests can assert on it.
     */
    private function registerStubRoute(
        string $method,
        string $path,
        string $returnValue = 'stub-ok'
    ): string {
        // PHP anonymous classes cannot be referenced by name after creation,
        // so we use a plain closure for stub routes.
        $this->router->add($method, $path, function () use ($returnValue): string {
            echo $returnValue;
            return $returnValue;
        });

        return $returnValue;
    }

    // ── Known route resolves correctly ────────────────────────────────────────

    /**
     * A registered GET route must be dispatched and its handler called.
     */
    public function testKnownGetRouteDispatchesCorrectly(): void
    {
        $this->router->add('GET', '/login', function (): string {
            return 'login-handler-called';
        });

        ob_start();
        $result = $this->router->dispatch('GET', '/login');
        ob_end_clean();

        $this->assertSame('login-handler-called', $result,
            'dispatch() must return the handler return value for a known GET route');
    }

    /**
     * A registered POST route must be dispatched when the method matches.
     */
    public function testKnownPostRouteDispatchesCorrectly(): void
    {
        $this->router->add('POST', '/login', function (): string {
            return 'post-login-handled';
        });

        ob_start();
        $result = $this->router->dispatch('POST', '/login');
        ob_end_clean();

        $this->assertSame('post-login-handled', $result);
    }

    /**
     * GET and POST for the same path must be dispatched independently.
     * Registering both and dispatching GET must not invoke the POST handler.
     */
    public function testGetAndPostHandlersAreRegisteredSeparately(): void
    {
        $this->router->add('GET',  '/login', function (): string { return 'get-handler'; });
        $this->router->add('POST', '/login', function (): string { return 'post-handler'; });

        ob_start();
        $getResult  = $this->router->dispatch('GET',  '/login');
        $postResult = $this->router->dispatch('POST', '/login');
        ob_end_clean();

        $this->assertSame('get-handler',  $getResult);
        $this->assertSame('post-handler', $postResult);
    }

    /**
     * Verify that the router resolves [ClassName, 'methodName'] array handlers.
     * We register an anonymous class that is already loaded (not requiring
     * autoloading) and confirm the router instantiates it and calls the method.
     */
    public function testArrayHandlerInstantiatesControllerAndCallsMethod(): void
    {
        // Declare a minimal stub controller in the global scope for this test.
        // We do it once, inside an if-guard to survive test re-runs.
        if (!class_exists(\Tests\Unit\Core\StubController::class, false)) {
            // Evaluated at runtime so we can use the real class syntax.
            eval('namespace Tests\Unit\Core; class StubController { public function index(): string { return "stub-index"; } }');
        }

        $this->router->add('GET', '/stub', [\Tests\Unit\Core\StubController::class, 'index']);

        ob_start();
        $result = $this->router->dispatch('GET', '/stub');
        ob_end_clean();

        $this->assertSame('stub-index', $result,
            'Array handler must resolve to controller instance and method call');
    }

    /**
     * The router normalises paths, so '/login', 'login', and '/login/' all
     * map to the same handler.
     */
    public function testPathNormalisationAllowsTrailingSlashAndNoLeadingSlash(): void
    {
        $this->router->add('GET', '/app', function (): string { return 'app-ok'; });

        ob_start();
        // Dispatch without leading slash — normalizePath should handle it.
        $result1 = $this->router->dispatch('GET', 'app');
        // Dispatch with trailing slash.
        $result2 = $this->router->dispatch('GET', '/app/');
        ob_end_clean();

        $this->assertSame('app-ok', $result1, 'Path without leading slash should resolve');
        $this->assertSame('app-ok', $result2, 'Path with trailing slash should resolve');
    }

    /**
     * Method matching is case-insensitive: 'get' dispatches the same as 'GET'.
     */
    public function testMethodMatchingIsCaseInsensitive(): void
    {
        $this->router->add('GET', '/case-test', function (): string { return 'case-ok'; });

        ob_start();
        $result = $this->router->dispatch('get', '/case-test');
        ob_end_clean();

        $this->assertSame('case-ok', $result,
            'dispatch() must normalise the HTTP method to uppercase');
    }

    // ── Unknown route returns 404 ─────────────────────────────────────────────

    /**
     * Dispatching a path that has no registered handler must set HTTP 404 and
     * output '404 Not Found', returning null.
     */
    public function testUnknownRouteReturns404(): void
    {
        // Register an unrelated route so the router is not empty.
        $this->router->add('GET', '/known', function (): string { return 'ok'; });

        ob_start();
        $result = $this->router->dispatch('GET', '/does-not-exist');
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code(),
            'dispatch() must set HTTP 404 for an unknown route');
        $this->assertStringContainsString('404', $output,
            'dispatch() must output a 404 message for an unknown route');
        $this->assertNull($result,
            'dispatch() must return null for an unknown route');
    }

    /**
     * Dispatching a path that IS registered but with the wrong HTTP method
     * must also yield a 404 — method+path must both match.
     */
    public function testWrongMethodForKnownPathReturns404(): void
    {
        $this->router->add('GET', '/actions', function (): string { return 'ok'; });

        http_response_code(200); // reset before this assertion

        ob_start();
        $result = $this->router->dispatch('POST', '/actions');
        ob_end_clean();

        $this->assertSame(404, http_response_code(),
            'A known path with the wrong method must return 404');
        $this->assertNull($result);
    }

    /**
     * An empty path (edge case) must not match any route unless one is
     * explicitly registered for '/'.
     */
    public function testEmptyPathReturns404WhenNoRootRouteRegistered(): void
    {
        $this->router->add('GET', '/login', function (): string { return 'ok'; });

        ob_start();
        $result = $this->router->dispatch('GET', '');
        ob_end_clean();

        // '/' and '' both normalise to '/', which has no handler here.
        // (Root '/' is NOT registered in this test.)
        $this->assertNull($result,
            'An empty URI with no root route registered must return null');
    }

    /**
     * Routes from the real index.php registration pattern must work.
     * This test mirrors the actual route table to confirm the router handles
     * them without error.
     */
    public function testRouteTablePatternMatchesExpectedPaths(): void
    {
        // Simulate the index.php pattern: add multiple routes.
        $dispatched = [];

        $this->router->add('GET',  '/app',     function () use (&$dispatched): void { $dispatched[] = 'dashboard'; });
        $this->router->add('GET',  '/tickets', function () use (&$dispatched): void { $dispatched[] = 'tickets'; });
        $this->router->add('GET',  '/assets',  function () use (&$dispatched): void { $dispatched[] = 'assets'; });

        ob_start();
        $this->router->dispatch('GET', '/app');
        $this->router->dispatch('GET', '/tickets');
        $this->router->dispatch('GET', '/assets');
        ob_end_clean();

        $this->assertSame(['dashboard', 'tickets', 'assets'], $dispatched,
            'All registered routes must dispatch to their respective handlers');
    }
}
