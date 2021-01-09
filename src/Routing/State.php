<?php

namespace MiladRahimi\PhpRouter\Routing;

class State
{
    const MIDDLEWARE = 'middleware';
    const PREFIX = 'prefix';
    const DOMAIN = 'domain';

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
     * Attributes constructor.
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
     * Create a state from the given array
     *
     * @param array $attributes
     * @return self
     */
    public static function createFromArray(array $attributes): self
    {
        return new static(
            $attributes[static::PREFIX ?? ''],
            $attributes[static::MIDDLEWARE ?? []],
            $attributes[static::DOMAIN ?? null]
        );
    }

    /**
     * Append new attributes to the existing ones
     *
     * @param array $attributes
     */
    public function append(array $attributes): void
    {
        $this->domain = $attributes[State::DOMAIN] ?? null;
        $this->prefix .= $attributes[State::PREFIX] ?? '';
        $this->middleware = array_merge($this->middleware, $attributes[State::MIDDLEWARE] ?? []);
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param array $middleware
     */
    public function setMiddleware(array $middleware): void
    {
        $this->middleware = $middleware;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }
}
