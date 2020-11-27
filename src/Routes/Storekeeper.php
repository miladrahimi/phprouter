<?php

namespace MiladRahimi\PhpRouter\Routes;

use MiladRahimi\PhpRouter\Attributes;

class Storekeeper
{
    /**
     * @var Store
     */
    private $repository;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * @var string|null
     */
    private $domain = null;

    /**
     * Storekeeper constructor.
     *
     * @param Store $repository
     */
    public function __construct(Store $repository)
    {
        $this->repository = $repository;
    }

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
        $path = $this->prefix . $path;
        $this->repository->save($method, $path, $controller, $name, $this->middleware, $this->domain);
    }

    /**
     * @param array $attributes
     */
    public function updateAttributes(array $attributes): void
    {
        $this->domain = $attributes[Attributes::DOMAIN];
        $this->prefix = $attributes[Attributes::PREFIX];
        $this->middleware = $attributes[Attributes::MIDDLEWARE];
    }

    /**
     * Append new attributes to the existing ones
     *
     * @param array $attributes
     */
    public function appendAttributes(array $attributes): void
    {
        $this->domain = $attributes[Attributes::DOMAIN] ?? null;
        $this->prefix .= $attributes[Attributes::PREFIX] ?? '';
        $this->middleware = array_merge($this->middleware, $attributes[Attributes::MIDDLEWARE] ?? []);
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            Attributes::DOMAIN => $this->domain,
            Attributes::PREFIX => $this->prefix,
            Attributes::MIDDLEWARE => $this->middleware,
        ];
    }

    /**
     * @return Store
     */
    public function getRepository(): Store
    {
        return $this->repository;
    }

    /**
     * @param Store $repository
     */
    public function setRepository(Store $repository): void
    {
        $this->repository = $repository;
    }
}
