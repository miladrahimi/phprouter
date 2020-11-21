<?php

namespace MiladRahimi\PhpRouter;

/**
 * Class Config
 *
 * @package MiladRahimi\PhpRouter\Values
 */
class Config
{
    /**
     * Path prefix
     *
     * @var string
     */
    private $prefix = '';

    /**
     * Middleware list
     *
     * @var string[]|callable[]
     */
    private $middleware = [];

    /**
     * Domain
     *
     * @var string|null
     */
    private $domain = null;

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
     * @param string $prefix
     */
    public function addPrefix(string $prefix): void
    {
        $this->prefix .= $prefix;
    }

    /**
     * @return string[]|callable[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param string[]|callable[] $middleware
     */
    public function setMiddleware(array $middleware): void
    {
        $this->middleware = $middleware;
    }

    /**
     * @param string[]|callable[] $middleware
     */
    public function addMiddleware(array $middleware): void
    {
        $this->middleware = array_merge($this->middleware, $middleware);
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
