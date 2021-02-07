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
use MiladRahimi\PhpRouter\Publisher\HttpPublisher;
use MiladRahimi\PhpRouter\View\PhpView;
use MiladRahimi\PhpRouter\Publisher\Publisher;
use MiladRahimi\PhpRouter\View\View;
use Psr\Container\ContainerInterface;
use Laminas\Diactoros\ServerRequestFactory;

/**
 * Class Router
 * It defines the application routes and dispatches them (runs the application).
 *
 * @package MiladRahimi\PhpRouter
 */
class Router
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Storekeeper
     */
    private $storekeeper;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var Caller
     */
    private $caller;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * List of defined parameter patterns with `pattern()` method
     *
     * @var string[]
     */
    private $patterns = [];

    /**
     * Constructor
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
     * Create a new Router instance
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

        return $container->instantiate(Router::class);
    }

    /**
     * Setup (enable) View
     * @link View
     *
     * @param string $directory
     */
    public function setupView(string $directory): void
    {
        $this->container->singleton(View::class, function () use ($directory) {
            return new PhpView($directory);
        });
    }

    /**
     * Group routes with the given common attributes
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
     * Dispatch routes (and run the application)
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

        $stack = array_merge($route->getMiddleware(), [$route->getController()]);
        $this->publisher->publish($this->caller->stack($stack, $request));
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
     * Define a new route
     *
     * @param string $method
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function define(string $method, string $path, $controller, ?string $name = null): void
    {
        $this->storekeeper->add($method, $path, $controller, $name);
    }

    /**
     * Map a controller to given route for all the http methods
     *
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function any(string $path, $controller, ?string $name = null): void
    {
        $this->define('*', $path, $controller, $name);
    }

    /**
     * Map a controller to given GET route
     *
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function get(string $path, $controller, ?string $name = null): void
    {
        $this->define('GET', $path, $controller, $name);
    }

    /**
     * Map a controller to given POST route
     *
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function post(string $path, $controller, ?string $name = null): void
    {
        $this->define('POST', $path, $controller, $name);
    }

    /**
     * Map a controller to given PUT route
     *
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function put(string $path, $controller, ?string $name = null): void
    {
        $this->define('PUT', $path, $controller, $name);
    }

    /**
     * Map a controller to given PATCH route
     *
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function patch(string $path, $controller, ?string $name = null): void
    {
        $this->define('PATCH', $path, $controller, $name);
    }

    /**
     * Map a controller to given DELETE route
     *
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function delete(string $path, $controller, ?string $name = null): void
    {
        $this->define('DELETE', $path, $controller, $name);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
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
