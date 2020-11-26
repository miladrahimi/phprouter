<?php

namespace MiladRahimi\PhpRouter;

use Closure;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpContainer\Exceptions\NotFoundException;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Routes\Route;
use MiladRahimi\PhpRouter\Routes\RouteManager;
use MiladRahimi\PhpRouter\Routes\RouteRepository;
use MiladRahimi\PhpRouter\Services\HttpPublisher;
use MiladRahimi\PhpRouter\Services\Publisher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;

/**
 * Class Router
 *
 * @package MiladRahimi\PhpRouter
 */
class Router
{
    /**
     * @var RouteManager
     */
    private $routeManager;

    /**
     * List of defined route parameter patterns
     *
     * @var string[]
     */
    private $patterns = [];

    /**
     * The dependency injection IoC container
     *
     * @var Container
     */
    private $container;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->routeManager = new RouteManager(new RouteRepository());
        $this->setupContainer();
    }

    /**
     * Group routes with the given attributes
     *
     * @param array $attributes
     * @param Closure $body
     */
    public function group(array $attributes, Closure $body): void
    {
        $oldAttributes = $this->routeManager->getAttributes();

        $this->routeManager->appendAttributes($attributes);

        call_user_func($body, $this);

        $this->routeManager->setAttributes($oldAttributes);
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
        $this->routeManager->add($method, $path, $controller, $name);
    }

    /**
     * Dispatch routes and run the application
     *
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     * @throws RouteNotFoundException
     */
    public function dispatch()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->container->get(ServerRequestInterface::class);

        $routes = $this->routeManager->getRepository()->findByMethod($request->getMethod());
        foreach ($routes as $route) {
            $parameters = [];
            if ($this->compare($route, $request, $parameters)) {
                $this->run($route, $parameters, $request);
                return;
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Run the route controllers and related middleware
     *
     * @param Route $route
     * @param array $parameters
     * @param ServerRequestInterface $request
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     */
    private function run(Route $route, array $parameters, ServerRequestInterface $request)
    {
        $route->setParameters($this->pruneRouteParameters($parameters));
        $route->setUri($request->getUri()->getPath());

        $this->container->singleton(Route::class, $route);

        foreach ($parameters as $key => $value) {
            $this->container->singleton('$' . $key, $value);
        }

        /** @var Publisher $publisher */
        $publisher = $this->container->get(Publisher::class);
        $publisher->publish($this->callStack(
            array_merge($route->getMiddleware(), [$route->getController()]),
            $request
        ));
    }

    /**
     * Prune route parameters (remove unnecessary parameters)
     *
     * @param array $parameters
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    private function pruneRouteParameters(array $parameters): array
    {
        return array_filter($parameters, function ($value, $name) {
            return is_numeric($name) == false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Call the given callable stack
     *
     * @param string[] $callables
     * @param ServerRequestInterface $request
     * @param int $i
     * @return ResponseInterface|mixed|null
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     */
    private function callStack(array $callables, ServerRequestInterface $request, $i = 0)
    {
        $this->container->singleton(ServerRequest::class, $request);
        $this->container->singleton(ServerRequestInterface::class, $request);

        if (isset($callables[$i + 1])) {
            $next = function (ServerRequestInterface $request) use ($callables, $i) {
                return $this->callStack($callables, $request, $i + 1);
            };

            $this->container->closure('$next', $next);
        }

        return $this->runCallable($callables[$i]);
    }

    /**
     * Run the given callable (controller or middleware)
     *
     * @param Closure|callable|string $callable
     * @return ResponseInterface|mixed|null
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     */
    private function runCallable($callable)
    {
        if (is_array($callable)) {
            if (count($callable) != 2) {
                throw new InvalidCallableException('Invalid callable: ' . implode(',', $callable));
            }

            list($class, $method) = $callable;

            if (class_exists($class) == false) {
                throw new InvalidCallableException("Class `$callable` not found.");
            }

            $object = new $class();

            if (method_exists($object, $method) == false) {
                throw new InvalidCallableException("Method `$class::$method` not found.");
            }

            $callable = [$object, $method];
        } else {
            if (is_string($callable)) {
                if (class_exists($callable)) {
                    $callable = new $callable();
                } else {
                    throw new InvalidCallableException("Class `$callable` not found.");
                }
            }

            if (is_object($callable) && !($callable instanceof Closure)) {
                if (method_exists($callable, 'handle')) {
                    $callable = [$callable, 'handle'];
                } else {
                    throw new InvalidCallableException("Method `$callable::handle` not found.");
                }
            }
        }

        if (is_callable($callable) == false) {
            throw new InvalidCallableException('Invalid callable.');
        }

        return $this->container->call($callable);
    }

    /**
     * Compare given route with the given http request
     *
     * @param Route $route
     * @param ServerRequestInterface $request
     * @param array $parameters
     * @return bool
     */
    private function compare(Route $route, ServerRequestInterface $request, array &$parameters): bool
    {
        return (
            $this->compareDomain($route->getDomain(), $request->getUri()->getHost()) &&
            $this->compareUri($route->getPath(), $request->getUri()->getPath(), $parameters)
        );
    }

    /**
     * Check if given request domain matches given route domain
     *
     * @param string|null $routeDomain
     * @param string $requestDomain
     * @return bool
     */
    private function compareDomain(?string $routeDomain, string $requestDomain): bool
    {
        return !$routeDomain || preg_match('@^' . $routeDomain . '$@', $requestDomain);
    }

    /**
     * Check if given request uri matches given uri method
     *
     * @param string $path
     * @param string $uri
     * @param array $parameters
     * @return bool
     */
    private function compareUri(string $path, string $uri, array &$parameters): bool
    {
        return preg_match('@^' . $this->regexUri($path) . '$@', $uri, $parameters);
    }

    /**
     * Convert route to regex
     *
     * @param string $route
     * @return string
     */
    private function regexUri(string $route): string
    {
        return preg_replace_callback('@{([^}]+)}@', function (array $match) {
            return $this->regexParameter($match[1]);
        }, $route);
    }

    /**
     * Convert route parameter to regex
     *
     * @param string $name
     * @return string
     */
    private function regexParameter(string $name): string
    {
        if ($name[-1] == '?') {
            $name = substr($name, 0, -1);
            $suffix = '?';
        } else {
            $suffix = '';
        }

        $pattern = $this->patterns[$name] ?? '[^/]+';

        return '(?<' . $name . '>' . $pattern . ')' . $suffix;
    }

    /**
     * Define a route parameter pattern
     *
     * @param string $name
     * @param string $pattern
     * @return self
     */
    public function pattern(string $name, string $pattern): self
    {
        $this->patterns[$name] = $pattern;

        return $this;
    }

    /**
     * Generate URL for given route name
     *
     * @param string $routeName
     * @param string[] $parameters
     * @return string
     * @throws UndefinedRouteException
     */
    public function url(string $routeName, array $parameters = []): string
    {
        if (!($route = $this->routeManager->getRepository()->findByName($routeName))) {
            throw new UndefinedRouteException("There is no route with name `$routeName`.");
        }

        $uri = $route->getPath();

        foreach ($parameters as $name => $value) {
            $uri = preg_replace('/\??{' . $name . '\??}/', $value, $uri);
        }

        $uri = preg_replace('/{[^}]+\?}/', '', $uri);
        $uri = str_replace('/?', '', $uri);

        return $uri;
    }

    /**
     * Setup IoC container
     */
    private function setupContainer(): void
    {
        $this->container = new Container();

        $this->container->singleton(Router::class, $this);

        $this->container->singleton(Container::class, $this->container);
        $this->container->singleton(ContainerInterface::class, $this->container);

        $this->container->singleton(Publisher::class, HttpPublisher::class);

        $request = ServerRequestFactory::fromGlobals();
        $this->container->singleton(ServerRequestInterface::class, $request);
        $this->container->singleton(ServerRequest::class, $request);
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
     * Map a controller to given OPTIONS route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     */
    public function options(string $path, $controller, ?string $name = null): void
    {
        $this->map('OPTIONS', $path, $controller, $name);
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
