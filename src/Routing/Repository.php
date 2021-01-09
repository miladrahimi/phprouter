<?php

namespace MiladRahimi\PhpRouter\Routing;

class Repository
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * Add a route to the repository
     *
     * @param string $method
     * @param string $path
     * @param $controller
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

        if ($name) {
            $this->routes['name'][$name] = $route;
        }
    }

    /**
     * Find routes by given method
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
     * Find route by given name
     *
     * @param string $name
     * @return Route|null
     */
    public function findByName(string $name): ?Route
    {
        return $this->routes['name'][$name] ?? null;
    }
}
