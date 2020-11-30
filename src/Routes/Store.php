<?php

namespace MiladRahimi\PhpRouter\Routes;

class Store
{
    /**
     * @var array
     */
    private $repository = [];

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

        $this->repository['method'][$method][] = $route;

        if ($name) {
            $this->repository['name'][$name] = $route;
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
            $this->repository['method']['*'] ?? [],
            $this->repository['method'][$method] ?? []
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
        return $this->repository['name'][$name] ?? null;
    }
}
