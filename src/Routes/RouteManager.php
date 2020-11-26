<?php

namespace MiladRahimi\PhpRouter\Routes;

use MiladRahimi\PhpRouter\Attributes;

/**
 * Class RouteManager
 *
 * @package MiladRahimi\PhpRouter
 */
class RouteManager
{
    private $repository;

    /**
     * @var array
     */
    private $attributes = [
        Attributes::PREFIX => '',
        Attributes::MIDDLEWARE => [],
        Attributes::DOMAIN => null,
    ];

    /**
     * RouteManager constructor.
     *
     * @param RouteRepository $repository
     */
    public function __construct(RouteRepository $repository)
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
        $this->repository->save(
            $method,
            $this->attributes[Attributes::PREFIX] . $path,
            $controller,
            $name,
            $this->attributes[Attributes::MIDDLEWARE],
            $this->attributes[Attributes::DOMAIN]
        );
    }

    /**
     * Append new attributes to the existing ones
     *
     * @param array $attributes
     */
    public function appendAttributes(array $attributes): void
    {
        $this->attributes[Attributes::DOMAIN] = $attributes[Attributes::DOMAIN] ?? null;
        $this->attributes[Attributes::PREFIX] .= $attributes[Attributes::PREFIX] ?? '';
        $this->attributes[Attributes::MIDDLEWARE] = array_merge(
            $this->attributes[Attributes::MIDDLEWARE],
            $attributes[Attributes::MIDDLEWARE] ?? []
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

    /**
     * @return RouteRepository
     */
    public function getRepository(): RouteRepository
    {
        return $this->repository;
    }

    /**
     * @param RouteRepository $repository
     */
    public function setRepository(RouteRepository $repository): void
    {
        $this->repository = $repository;
    }
}
