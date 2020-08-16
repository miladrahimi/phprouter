<?php

namespace MiladRahimi\PhpRouter\Values;

use Closure;
use MiladRahimi\PhpRouter\Middleware;

/**
 * Class Route
 *
 * @package MiladRahimi\PhpRouter
 */
class Route
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $method;

    /**
     * @var Closure|callable|string
     */
    private $controller;

    /**
     * @var string[]|callable[]|Closure[]|Middleware[]
     */
    private $middleware;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var null|string
     */
    private $uri = null;

    /**
     * @var null|string[]|array[string]string
     */
    private $parameters = null;

    /**
     * Route constructor.
     *
     * @param string|null $name
     * @param string $uri
     * @param string|null $method
     * @param Closure|callable|string $controller
     * @param string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     */
    public function __construct(
        ?string $name,
        string $uri,
        string $method,
        $controller,
        $middleware,
        ?string $domain
    )
    {
        $this->name = $name;
        $this->path = $uri;
        $this->method = $method;
        $this->controller = $controller;
        $this->middleware = $middleware;
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'uri' => $this->path,
            'method' => $this->method,
            'controller' => $this->controller,
            'middleware' => $this->middleware,
            'domain' => $this->domain,
        ];
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Closure|callable|string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string[]|callable[]|Closure[]|Middleware[]
     */
    public function getMiddleware()
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

    /**
     * @return array|string[]|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param array|string[]|null $parameters
     */
    public function setParameters(?array $parameters): void
    {
        $routeParameters = [];

        foreach ($parameters as $key => $value) {
            if (strpos($this->path, $key) !== false) {
                $routeParameters[$key] = $value;
            }
        }

        $this->parameters = $routeParameters;
    }

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /** 
     * @param string|null $uri
     */
    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }
}
