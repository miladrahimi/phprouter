<?php

namespace MiladRahimi\PhpRouter;

use Closure;

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
     * @var array|Closure
     */
    private $controller;

    /**
     * @var array[]|Closure[]
     */
    private $middleware;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var string
     */
    private $uri = null;

    /**
     * @var string[]
     */
    private $parameters = [];

    /**
     * Route constructor.
     *
     * @param string|null $name
     * @param string $path
     * @param string|null $method
     * @param Closure|array $controller
     * @param string[]|callable[] $middleware
     * @param string|null $domain
     */
    public function __construct(
        ?string $name,
        string $path,
        string $method,
        $controller,
        array $middleware,
        ?string $domain
    )
    {
        $this->name = $name;
        $this->path = $path;
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
            'path' => $this->path,
            'method' => $this->method,
            'controller' => $this->controller,
            'middleware' => $this->middleware,
            'domain' => $this->domain,
            'uri' => $this->uri,
            'parameters' => $this->parameters,
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
     * @return array|Closure
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return array[]|Closure[]
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
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }
}
