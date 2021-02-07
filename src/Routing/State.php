<?php

namespace MiladRahimi\PhpRouter\Routing;

/**
 * Class State
 * It is state (attributes) of routes
 *
 * @package MiladRahimi\PhpRouter\Routing
 */
class State
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
     */
    private $middleware;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * Constructor
     *
     * @param string $prefix
     * @param array $middleware
     * @param string|null $domain
     */
    public function __construct(string $prefix = '', array $middleware = [], ?string $domain = null)
    {
        $this->prefix = $prefix;
        $this->middleware = $middleware;
        $this->domain = $domain;
    }

    /**
     * Append new attributes to the existing ones
     *
     * @param array $attributes
     */
    public function append(array $attributes): void
    {
        $this->domain = $attributes[Attributes::DOMAIN] ?? null;
        $this->prefix .= $attributes[Attributes::PREFIX] ?? '';
        $this->middleware = array_merge($this->middleware, $attributes[Attributes::MIDDLEWARE] ?? []);
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
