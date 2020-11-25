<?php

namespace MiladRahimi\PhpRouter;

use Closure;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpContainer\Exceptions\NotFoundException;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
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
     * @var Collection
     */
    private $collection;

    /**
     * List of defined route parameters
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
        $this->collection = new Collection();
        $this->container = new Container();

        $this->bind([Router::class], $this);
        $this->bind([Container::class, ContainerInterface::class], $this->container);
        $this->bind([Publisher::class], HttpPublisher::class);
        $this->bind([ServerRequestInterface::class, ServerRequest::class], ServerRequestFactory::fromGlobals());
    }

    /**
     * Group routes with the given attributes
     *
     * @param array $attributes
     * @param Closure $body
     * @return self
     */
    public function group(array $attributes, Closure $body): self
    {
        $oldAttributes = $this->collection->getAttributes();
        $this->collection->appendAttributes($attributes);

        call_user_func($body, $this);

        // Revert to the old config
        $this->collection->setAttributes($oldAttributes);

        return $this;
    }

    /**
     * Map a controller to a route
     *
     * @param string $method
     * @param string $path
     * @param Closure|array $controller
     * @param string|null $name
     * @return self
     */
    public function map(string $method, string $path, $controller, ?string $name = null): self
    {
        $this->collection->add($method, $path, $controller, $name);

        return $this;
    }

    /**
     * Dispatch routes and run the application
     *
     * @return self
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     * @throws RouteNotFoundException
     */
    public function dispatch(): self
    {
        /** @var ServerRequestInterface $request */
        $request = $this->container->get(ServerRequestInterface::class);

        foreach ($this->collection->findByMethod($request->getMethod()) as $route) {
            $parameters = [];
            if ($this->compare($route, $request, $parameters)) {
                $this->run($route, $parameters, $request);

                return $this;
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

        $this->bind([Route::class], $route);

        foreach ($parameters as $key => $value) {
            $this->bind(['$' . $key], $value);
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
        $this->container->singleton('$request', $request);
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
    public function patterns(string $name, string $pattern): self
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
        if (!($route = $this->collection->findByName($routeName))) {
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
     * Bind dependencies using the container
     *
     * @param array $abstracts
     * @param $concrete
     */
    private function bind(array $abstracts, $concrete): void
    {
        foreach ($abstracts as $abstract) $this->container->singleton($abstract, $concrete);
    }

    /**
     * Map a controller to given route with multiple http methods
     *
     * @param array $methods
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function match(array $methods, string $path, $controller, ?string $name = null): self
    {
        foreach ($methods as $method) {
            return $this->map($method, $path, $controller, $name);
        }

        return $this;
    }

    /**
     * Map a controller to given route for all the http methods
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function any(string $path, $controller, ?string $name = null): self
    {
        return $this->map('*', $path, $controller, $name);
    }

    /**
     * Map a controller to given GET route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function get(string $path, $controller, ?string $name = null): self
    {
        return $this->map('GET', $path, $controller, $name);
    }

    /**
     * Map a controller to given POST route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function post(string $path, $controller, ?string $name = null): self
    {
        return $this->map('POST', $path, $controller, $name);
    }

    /**
     * Map a controller to given PUT route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function put(string $path, $controller, ?string $name = null): self
    {
        return $this->map('PUT', $path, $controller, $name);
    }

    /**
     * Map a controller to given PATCH route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function patch(string $path, $controller, ?string $name = null): self
    {
        return $this->map('PATCH', $path, $controller, $name);
    }

    /**
     * Map a controller to given DELETE route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function delete(string $path, $controller, ?string $name = null): self
    {
        return $this->map('DELETE', $path, $controller, $name);
    }

    /**
     * Map a controller to given OPTIONS route
     *
     * @param string $path
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function options(string $path, $controller, ?string $name = null): self
    {
        return $this->map('OPTIONS', $path, $controller, $name);
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
