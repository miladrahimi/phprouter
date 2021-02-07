<?php

namespace MiladRahimi\PhpRouter\Routing;

use Closure;

/**
 * Class Repository
 * It is a repository for the defined routes
 *
 * @package MiladRahimi\PhpRouter\Routing
 */
class Repository
{
    /**
     * List of the defined routes
     *
     * @var Route[]
     */
    private $routes = [];

    /**
     * Save a new route
     *
     * @param string $method
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     * @param array $middleware
     * @param string|null $domain
     */
    public function save(
        string $method,
        string $path,
        $controller,
        ?string $name,
        array $middleware,
        ?string $domain
    ): void
    {
        $route = new Route($method, $path, $controller, $name, $middleware, $domain);

        $this->routes['method'][$method][] = $route;

        if ($name !== null) {
            $this->routes['name'][$name] = $route;
        }
    }

    /**
     * Find routes by method
     *
     * @param string $method
     * @return Route[]
     */
    public function findByMethod(string $method): array
    {
        $routes = array_merge(
            $this->routes['method']['*'] ?? [],
            $this->routes['method'][$method] ?? []
        );

        krsort($routes);

        return $routes;
    }

    /**
     * Find route by name
     *
     * @param string $name
     * @return Route|null
     */
    public function findByName(string $name): ?Route
    {
        return $this->routes['name'][$name] ?? null;
    }
}
