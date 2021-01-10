<?php

namespace MiladRahimi\PhpRouter;

use Closure;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpRouter\Dispatching\Caller;
use MiladRahimi\PhpRouter\Dispatching\Matcher;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Routing\Route;
use MiladRahimi\PhpRouter\Routing\Storekeeper;
use MiladRahimi\PhpRouter\Routing\Repository;
use MiladRahimi\PhpRouter\Services\HttpPublisher;
use MiladRahimi\PhpRouter\Services\Publisher;
use Psr\Container\ContainerInterface;
use Laminas\Diactoros\ServerRequestFactory;
use RuntimeException;

class Router
{
    /**
     * The dependency injection IoC container
     *
     * @var Container
     */
    private $container;

    /**
     * The storekeeper of route repository
     *
     * @var Storekeeper
     */
    private $storekeeper;

    /**
     * The route matcher that finds appropriate routes for requests
     *
     * @var Matcher
     */
    private $matcher;

    /**
     * The callable caller that calls (invokes) middleware and controllers
     *
     * @var Caller
     */
    private $caller;

    /**
     * The publisher that publish controller outputs
     *
     * @var Publisher
     */
    private $publisher;

    /**
     * List of defined parameter patterns
     *
     * @var string[]
     */
    private $patterns = [];

    /**
     * Router constructor.
     *
     * @param Container $container
     * @param Storekeeper $storekeeper
     * @param Matcher $matcher
     * @param Caller $caller
     * @param Publisher $publisher
     */
    public function __construct(
        Container $container,
        Storekeeper $storekeeper,
        Matcher $matcher,
        Caller $caller,
        Publisher $publisher
    )
    {
        $this->container = $container;
        $this->storekeeper = $storekeeper;
        $this->matcher = $matcher;
        $this->caller = $caller;
        $this->publisher = $publisher;
    }

    /**
     * Create a new router instance
     *
     * @return static
     */
    public static function create(): self
    {
        $container = new Container();
        $container->singleton(Container::class, $container);
        $container->singleton(ContainerInterface::class, $container);
        $container->singleton(Repository::class, new Repository());
        $container->singleton(Publisher::class, HttpPublisher::class);

        try {
            return $container->instantiate(Router::class);
        } catch (ContainerException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Group routes with the given attributes
     *
     * @param array $attributes
     * @param Closure $body
     */
    public function group(array $attributes, Closure $body): void
    {
        $oldState = clone $this->storekeeper->getState();

        $this->storekeeper->getState()->append($attributes);

        call_user_func($body, $this);

        $this->storekeeper->setState($oldState);
    }

    /**
     * Map a controller to a route
     *
     * @param string $method
     * @param string $path
     * @param Closure|array $controller
     * @param string|null $name
     */
    public function map(string $method, string $path, $controller, ?string $name = null): void
    {
        $this->storekeeper->add($method, $path, $controller, $name);
    }

    /**
     * Dispatch routes and run the application
     *
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws RouteNotFoundException
     */
    public function dispatch()
    {
        $request = ServerRequestFactory::fromGlobals();

        $route = $this->matcher->find($request, $this->patterns);

        $this->container->singleton(Route::class, $route);

        foreach ($route->getParameters() as $key => $value) {
            $this->container->singleton('$' . $key, $value);
        }

        $this->publisher->publish($this->caller->stack(
            array_merge($route->getMiddleware(), [$route->getController()]),
            $request
        ));
    }

    /**
     * Define a parameter pattern
     *
     * @param string $name
     * @param string $pattern
     */
    public function pattern(string $name, string $pattern)
    {
        $this->patterns[$name] = $pattern;
    }

    /**
     * Map a controller to given route with multiple http methods
     *
     * @param array $methods
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function match(array $methods, string $path, $controller, ?string $name = null): void
    {
        foreach ($methods as $method) {
            $this->map($method, $path, $controller, $name);
        }
    }

    /**
     * Map a controller to given route for all the http methods
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function any(string $path, $controller, ?string $name = null): void
    {
        $this->map('*', $path, $controller, $name);
    }

    /**
     * Map a controller to given GET route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function get(string $path, $controller, ?string $name = null): void
    {
        $this->map('GET', $path, $controller, $name);
    }

    /**
     * Map a controller to given POST route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function post(string $path, $controller, ?string $name = null): void
    {
        $this->map('POST', $path, $controller, $name);
    }

    /**
     * Map a controller to given PUT route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function put(string $path, $controller, ?string $name = null): void
    {
        $this->map('PUT', $path, $controller, $name);
    }

    /**
     * Map a controller to given PATCH route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function patch(string $path, $controller, ?string $name = null): void
    {
        $this->map('PATCH', $path, $controller, $name);
    }

    /**
     * Map a controller to given DELETE route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function delete(string $path, $controller, ?string $name = null): void
    {
        $this->map('DELETE', $path, $controller, $name);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return Publisher
     */
    public function getPublisher(): Publisher
    {
        return $this->publisher;
    }

    /**
     * @param Publisher $publisher
     */
    public function setPublisher(Publisher $publisher): void
    {
        $this->publisher = $publisher;
    }
}
