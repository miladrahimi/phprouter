<?php

namespace MiladRahimi\PhpRouter\Routing;

/**
 * It is state (attributes) of routes
 */
class State
{
    private string $prefix;

    private array $middleware;

    private ?string $domain;

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
     */
    public function append(array $attributes): void
    {
        $this->domain = $attributes[Attributes::DOMAIN] ?? null;
        $this->prefix .= $attributes[Attributes::PREFIX] ?? '';
        $this->middleware = array_merge($this->middleware, $attributes[Attributes::MIDDLEWARE] ?? []);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
