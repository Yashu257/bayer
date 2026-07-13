<?php

declare(strict_types=1);

namespace Core\Router;

use Core\Http\Request;
use Core\Http\Response;
use Core\Exceptions\HttpException;

/**
 * Router — maps URI + method to a controller action through a middleware pipeline.
 */
class Router
{
    private array $routes          = [];
    private array $middlewareMap   = [];

    // --- Route registration --------------------------------------------------

    public function get(string $uri, string $action, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, string $action, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, string $action, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, string $action, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    private function addRoute(string $method, string $uri, string $action, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'uri'        => rtrim($uri, '/') ?: '/',
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    public function setMiddlewareMap(array $map): void
    {
        $this->middlewareMap = $map;
    }

    // --- Dispatch ------------------------------------------------------------

    public function dispatch(Request $request): void
    {
        // Support PUT / DELETE via _method override from forms
        $method = $request->input('_method')
            ? strtoupper($request->input('_method'))
            : $request->method();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchUri($route['uri'], $request->uri());
            if ($params === null) {
                continue;
            }

            // Bind extracted route params as request attributes
            foreach ($params as $key => $value) {
                $request->setAttribute($key, $value);
            }

            // Build and execute the middleware + controller pipeline
            $this->runPipeline($request, $route['action'], $route['middleware']);
            return;
        }

        // No route matched
        throw new HttpException(404, 'Page not found.');
    }

    // --- URI matching --------------------------------------------------------

    /** Returns array of named params on match, null on no match. */
    private function matchUri(string $routeUri, string $requestUri): ?array
    {
        $routeUri   = rtrim($routeUri,   '/') ?: '/';
        $requestUri = rtrim($requestUri, '/') ?: '/';

        // Convert {param} placeholders to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestUri, $matches)) {
            return null;
        }

        // Keep only named captures
        return array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
    }

    // --- Middleware pipeline --------------------------------------------------

    private function runPipeline(Request $request, string $action, array $routeMiddleware): void
    {
        $named  = $this->middlewareMap['named'] ?? [];
        $global = $this->middlewareMap['global'] ?? [];

        // Determine group from request path prefix
        $groupKey = $this->resolveGroup($request->uri());
        $group    = $this->middlewareMap['groups'][$groupKey] ?? [];

        // Build the full middleware stack: global → group → route-specific
        $stack = array_merge($global, $group, $this->resolveNamed($routeMiddleware, $named));

        // Create the final handler: the controller action
        $handler = fn(Request $req) => $this->callAction($req, $action);

        // Wrap handler in middleware (last in → first to call next)
        $pipeline = array_reduce(
            array_reverse($stack),
            function (callable $next, string $class) use ($named, $routeMiddleware): callable {
                return function (Request $req) use ($class, $next, $routeMiddleware): mixed {
                    // Parse param for parameterised middleware (e.g. admin.role:super_admin)
                    $param = null;
                    foreach ($routeMiddleware as $mw) {
                        if (str_contains($mw, ':') && str_starts_with($mw, $this->getMiddlewareKey($class))) {
                            [, $param] = explode(':', $mw, 2);
                        }
                    }
                    $instance = new $class();
                    return $instance->handle($req, $next, $param);
                };
            },
            $handler
        );

        $response = $pipeline($request);
        if ($response instanceof Response) {
            $response->send();
        }
    }

    private function resolveGroup(string $uri): string
    {
        if (str_starts_with($uri, '/api/')) {
            return 'api';
        }
        if (str_starts_with($uri, '/admin')) {
            return 'admin';
        }
        return 'web';
    }

    /** Resolve named middleware keys to class names, stripping param suffixes. */
    private function resolveNamed(array $keys, array $namedMap): array
    {
        $classes = [];
        foreach ($keys as $key) {
            $base = str_contains($key, ':') ? explode(':', $key, 2)[0] : $key;
            if (isset($namedMap[$base])) {
                $classes[] = $namedMap[$base];
            }
        }
        return $classes;
    }

    private function getMiddlewareKey(string $class): string
    {
        $named = $this->middlewareMap['named'] ?? [];
        return (string) array_search($class, $named, true);
    }

    // --- Controller dispatch -------------------------------------------------

    private function callAction(Request $request, string $action): mixed
    {
        [$controllerName, $method] = explode('@', $action, 2);

        // Build fully-qualified class name from short name (e.g. 'Auth\LoginController')
        $class = 'App\\Controllers\\' . str_replace('/', '\\', $controllerName);

        if (!class_exists($class)) {
            throw new HttpException(500, "Controller not found: $class");
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            throw new HttpException(500, "Method $method not found on $class");
        }

        return $controller->$method($request);
    }
}
