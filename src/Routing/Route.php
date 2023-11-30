<?php

namespace MiladRahimi\PhpRouter\Routing;

use Closure;

/**
 * It is a single defined route
 */
class Route
{
    /**
     * The route name
     */
    private ?string $name;

    /**
     * The route path
     */
    private string $path;

    /**
     * The route http method
     */
    private ?string $method;

    /**
     * The route controller
     *
     * @var Closure|string|array
     */
    private $controller;

    /**
     * The route middleware
     */
    private array $middleware;

    /**
     * The route domain
     */
    private ?string $domain;

    /**
     * The route uri from user request (post-property)
     */
    private ?string $uri = null;

    /**
     * The route parameters from user request (post-property)
     *
     * @var string[]
     */
    private array $parameters = [];

    /**
     * Constructor
     *
     * @param string|null $name
     * @param string $path
     * @param string|null $method
     * @param Closure|string|array $controller
     * @param array $middleware
     * @param string|null $domain
     */
    public function __construct(
        string $method,
        string $path,
        $controller,
        ?string $name,
        array $middleware,
        ?string $domain
    )
    {
        $this->method = $method;
        $this->path = $path;
        $this->controller = $controller;
        $this->name = $name;
        $this->middleware = $middleware;
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->getMethod(),
            'path' => $this->getPath(),
            'controller' => $this->getController(),
            'name' => $this->getName(),
            'middleware' => $this->getMiddleware(),
            'domain' => $this->getDomain(),
            'uri' => $this->getUri(),
            'parameters' => $this->getParameters(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }
}
