<?php

namespace MiladRahimi\PhpRouter;

/**
 * Class Route
 *
 * @package MiladRahimi\PhpRouter
 */
class Route
{
    const URI = 'uri';
    const METHOD = 'method';
    const CONTROLLER = 'controller';
    const MIDDLEWARE = 'middleware';
    const DOMAIN = 'domain';
    const PREFIX = 'prefix';
    const NAME = 'name';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string|null
     */
    private $method;

    /**
     * @var string|callable
     */
    private $controller;

    /**
     * @var string[]|callable[]
     */
    private $middleware;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * Route constructor.
     *
     * @param string|null $name
     * @param string $uri
     * @param string|null $method
     * @param $controller
     * @param $middleware
     * @param string|null $domain
     */
    public function __construct(
        ?string $name,
        string $uri,
        ?string $method,
        $controller,
        $middleware,
        ?string $domain
    ) {
        $this->name = $name;
        $this->uri = $uri;
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
            'uri' => $this->uri,
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
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
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

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string|null $method
     */
    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return callable|string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param callable|string $controller
     */
    public function setController($controller): void
    {
        $this->controller = $controller;
    }

    /**
     * @return callable[]|string[]
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @param callable[]|string[] $middleware
     */
    public function setMiddleware($middleware): void
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
