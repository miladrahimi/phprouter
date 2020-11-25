<?php

namespace MiladRahimi\PhpRouter;

class Collection
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * List of named routes
     *
     * @var Route[]
     */
    private $names = [];

    /**
     * @var array
     */
    private $attributes = [
        Attributes::PREFIX => '',
        Attributes::MIDDLEWARE => [],
        Attributes::DOMAIN => null,
    ];

    /**
     * Add a route to the collection
     *
     * @param string $method
     * @param string $path
     * @param $controller
     * @param string|null $name
     */
    public function add(string $method, string $path, $controller, ?string $name = null): void
    {
        $path = $this->attributes[Attributes::PREFIX] . $path;
        $route = new Route(
            $name,
            $path,
            $method,
            $controller,
            $this->attributes[Attributes::MIDDLEWARE],
            $this->attributes[Attributes::DOMAIN]
        );

        $this->routes[$method][] = $route;

        $name && $this->names[$name] = $route;
    }

    /**
     * Find routes by given method
     *
     * @param string $method
     * @return Route[]
     */
    public function findByMethod(string $method): array
    {
        $routes = array_merge($this->routes['*'] ?? [], $this->routes[$method] ?? []);

        sort($routes, SORT_DESC);

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
        return $this->names[$name] ?? null;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return Route[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    public function appendAttributes(array $newAttributes): void
    {
        $this->attributes[Attributes::PREFIX] .= $newAttributes[Attributes::PREFIX] ?? '';
        $this->attributes[Attributes::DOMAIN] = $newAttributes[Attributes::DOMAIN] ?? null;
        $this->attributes[Attributes::MIDDLEWARE] = array_merge(
            $this->attributes[Attributes::MIDDLEWARE],
            $newAttributes[Attributes::MIDDLEWARE] ?? []
        );
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}
