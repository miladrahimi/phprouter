<?php

namespace MiladRahimi\PhpRouter\Routing;

use Closure;

/**
 * Class Route
 * It is a single defined route
 *
 * @package MiladRahimi\PhpRouter\Routing
 */
class Route
{
    /**
     * The route name
     *
     * @var string|null
     */
    private $name;

    /**
     * The route path
     *
     * @var string
     */
    private $path;

    /**
     * The route http method
     *
     * @var string
     */
    private $method;

    /**
     * The route controller
     *
     * @var Closure|string|array
     */
    private $controller;

    /**
     * The route middleware
     *
     * @var array
     */
    private $middleware;

    /**
     * The route domain
     *
     * @var string|null
     */
    private $domain;

    /**
     * The route uri from user request (post-property)
     *
     * @var string
     */
    private $uri = null;

    /**
     * The route parameters from user request (post-property)
     *
     * @var string[]
     */
    private $parameters = [];

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
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Closure|string|array
     */
    public function getController()
    {
        return $this->controller;
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

    /**
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string[] $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return ?string
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * @param ?string $uri
     */
    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }
}
