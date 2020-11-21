<?php

namespace MiladRahimi\PhpRouter;

use Closure;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpContainer\Exceptions\NotFoundException;
use MiladRahimi\PhpRouter\Enums\GroupAttributes;
use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Services\HttpPublisher;
use MiladRahimi\PhpRouter\Services\Publisher;
use MiladRahimi\PhpRouter\Route;
use MiladRahimi\PhpRouter\Config;
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
     * List of declared routes
     *
     * @var Route[][]|array[string][int]Route
     */
    private $routes = [];

    /**
     * List of named routes
     *
     * @var Route[]|array[string]Route
     */
    private $names = [];

    /**
     * List of defined route parameters
     *
     * @var string[]|array[string]string
     */
    private $parameters = [];

    /**
     * User HTTP Request
     *
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * The publisher that is going to publish outputs of controllers
     *
     * @var Publisher
     */
    private $publisher;

    /**
     * The configuration of current instance/group
     * It holds current attributes like prefix, domain...
     *
     * @var Config
     */
    private $config;

    /**
     * The dependency injection IoC container
     *
     * @var Container
     */
    private $container;

    /**
     * Router constructor.
     *
     * @param Config|null $config
     */
    public function __construct(?Config $config = null)
    {
        $this->config = $config ?: new Config();

        $this->container = new Container();
        $this->publisher = new HttpPublisher();
        $this->request = ServerRequestFactory::fromGlobals();
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
        // Backup group config
        $config = clone $this->config;

        // Set middleware for the group
        if (isset($attributes[GroupAttributes::MIDDLEWARE])) {
            $this->config->addMiddleware($attributes[GroupAttributes::MIDDLEWARE]);
        }

        // Set prefix for the group
        if (isset($attributes[GroupAttributes::PREFIX])) {
            $this->config->addPrefix($attributes[GroupAttributes::PREFIX]);
        }

        // Set domain for the group
        if (isset($attributes[GroupAttributes::DOMAIN])) {
            $this->config->setDomain($attributes[GroupAttributes::DOMAIN]);
        }

        // Run the group body closure
        call_user_func($body, $this);

        // Revert to the old config
        $this->config = $config;

        return $this;
    }

    /**
     * Map a controller to a route and set basic attributes
     *
     * @param string $method
     * @param string $path
     * @param Closure|array $controller
     * @param string|null $name
     * @return self
     */
    public function map(string $method, string $path, $controller, ?string $name = null): self
    {
        $route = new Route(
            $name,
            $this->config->getPrefix() . $path,
            $method,
            $controller,
            $this->config->getMiddleware(),
            $this->config->getDomain()
        );

        $this->routes[$method][] = $route;

        $name && $this->names[$name] = $route;

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
        $domain = $this->request->getUri()->getHost();
        $uri = $this->request->getUri()->getPath();

        foreach ($this->routesForMethod($this->request->getMethod()) as $route) {
            $parameters = [];

            if (
                (!$route->getDomain() || $this->compareDomain($route->getDomain(), $domain)) &&
                $this->compareUri($route->getPath(), $uri, $parameters)
            ) {
                $route->setParameters($this->filterRouteParameters($parameters));
                $route->setUri($uri);

                $this->publisher->publish($this->run($route, $parameters, $this->request));

                return $this;
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Get all candidate routes for current http method
     *
     * @param string $method
     * @return Route[]
     */
    private function routesForMethod(string $method): array
    {
        $routes = array_merge($this->routes['*'] ?? [], $this->routes[$method] ?? []);
        sort($routes, SORT_DESC);

        return $routes;
    }

    /**
     * Filter route parameters and remove unnecessary parameters
     *
     * @param array $parameters
     * @return array
     */
    private function filterRouteParameters(array $parameters): array
    {
        return array_filter($parameters, function ($value, $name) {
            return isset($value) && is_numeric($name) == false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Run the controller of the given route
     *
     * @param Route $route
     * @param array $parameters
     * @param ServerRequestInterface $request
     * @return ResponseInterface|mixed|null
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     */
    private function run(Route $route, array $parameters, ServerRequestInterface $request)
    {
        $this->container->singleton('$container', $this->container);
        $this->container->singleton(Container::class, $this->container);
        $this->container->singleton(ContainerInterface::class, $this->container);

        $this->container->singleton('$router', $this);
        $this->container->singleton(Router::class, $this);

        $this->container->singleton('$route', $route);
        $this->container->singleton(Route::class, $route);

        foreach ($parameters as $key => $value) {
            $this->container->singleton('$' . $key, $value);
        }

        return $this->runStack(array_merge($route->getMiddleware(), [$route->getController()]), $request);
    }

    /**
     * Run the controller through the middleware (list)
     *
     * @param string[] $callables
     * @param ServerRequestInterface $request
     * @param int $i
     * @return ResponseInterface|mixed|null
     * @throws ContainerException
     * @throws InvalidCallableException
     * @throws NotFoundException
     */
    private function runStack(array $callables, ServerRequestInterface $request, $i = 0)
    {
        $this->container->singleton('$request', $request);
        $this->container->singleton(ServerRequest::class, $request);
        $this->container->singleton(ServerRequestInterface::class, $request);

        if (isset($callables[$i + 1])) {
            $next = function (ServerRequestInterface $request) use ($callables, $i) {
                return $this->runStack($callables, $request, $i + 1);
            };

            $this->container->closure('$next', $next);
        }

        return $this->runCallable($callables[$i]);
    }

    /**
     * Run the given callable (method, closure, etc.)
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
     * Check if given request domain matches given route domain
     *
     * @param string|null $routeDomain
     * @param string $requestDomain
     * @return bool
     */
    private function compareDomain(?string $routeDomain, string $requestDomain): bool
    {
        return preg_match('@^' . $routeDomain . '$@', $requestDomain);
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

        $pattern = $this->parameters[$name] ?? '[^/]+';

        return '(?<' . $name . '>' . $pattern . ')' . $suffix;
    }

    /**
     * Define a route parameter pattern
     *
     * @param string $name
     * @param string $pattern
     * @return self
     */
    public function define(string $name, string $pattern): self
    {
        $this->parameters[$name] = $pattern;

        return $this;
    }

    /**
     * Generate URL for given route name
     *
     * @param string $route
     * @param string[] $parameters
     * @return string
     * @throws UndefinedRouteException
     */
    public function url(string $route, array $parameters = []): string
    {
        if (isset($this->names[$route]) == false) {
            throw new UndefinedRouteException("There is no route with name `$route`.");
        }

        $uri = $this->names[$route]->getPath();

        foreach ($parameters as $name => $value) {
            $uri = preg_replace('/\??{' . $name . '\??}/', $value, $uri);
        }

        $uri = preg_replace('/{[^}]+\?}/', '', $uri);
        $uri = str_replace('/?', '', $uri);

        return $uri;
    }

    /**
     * Map a controller to given route for all the http methods
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function any(string $route, $controller, ?string $name = null): self
    {
        return $this->map('*', $route, $controller, $name);
    }

    /**
     * Map a controller to given GET route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function get(string $route, $controller, ?string $name = null): self
    {
        return $this->map(HttpMethods::GET, $route, $controller, $name);
    }

    /**
     * Map a controller to given POST route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function post(string $route, $controller, ?string $name = null): self
    {
        return $this->map(HttpMethods::POST, $route, $controller, $name);
    }

    /**
     * Map a controller to given PUT route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function put(string $route, $controller, ?string $name = null): self
    {
        return $this->map(HttpMethods::PUT, $route, $controller, $name);
    }

    /**
     * Map a controller to given PATCH route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function patch(string $route, $controller, ?string $name = null): self
    {
        return $this->map(HttpMethods::PATCH, $route, $controller, $name);
    }

    /**
     * Map a controller to given DELETE route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|null $name
     * @return self
     */
    public function delete(string $route, $controller, ?string $name = null): self
    {
        return $this->map(HttpMethods::DELETE, $route, $controller, $name);
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * @return Publisher|null
     */
    public function getPublisher(): ?Publisher
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

    /**
     * @param Container $container
     */
    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }
}
