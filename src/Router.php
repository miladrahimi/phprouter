<?php

namespace MiladRahimi\PhpRouter;

use Closure;
use MiladRahimi\PhpRouter\Enums\GroupAttributes;
use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Exceptions\InvalidControllerException;
use MiladRahimi\PhpRouter\Exceptions\InvalidMiddlewareException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Services\HttpPublisher;
use MiladRahimi\PhpRouter\Services\Publisher;
use MiladRahimi\PhpRouter\Values\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class Router
 * Router is the main class in the package.
 * It's responsible for defining and dispatching routes.
 *
 * @package MiladRahimi\PhpRouter
 */
class Router
{
    /**
     * List of defined routes
     *
     * @var Route[]
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
     * Name for the next route
     *
     * @var string|null
     */
    private $name = null;

    /**
     * Middleware (one or more) for next routes
     *
     * @var string[]|callable[]|Closure[]|Middleware[]
     */
    private $middleware = [];

    /**
     * URI prefix for next routes
     *
     * @var string
     */
    private $prefix;

    /**
     * Controller namespace prefix for next routes
     *
     * @var string
     */
    private $namespace;

    /**
     * Domain for next routes
     *
     * @var string|null
     */
    private $domain = null;

    /**
     * Current route that is recognized for current request
     *
     * @var Route|null
     */
    private $currentRoute = null;

    /**
     * Router constructor.
     *
     * @param string $uriPrefix
     * @param string $namespacePrefix
     */
    public function __construct(string $uriPrefix = '', string $namespacePrefix = '')
    {
        $this->prefix = $uriPrefix;
        $this->namespace = $namespacePrefix;
    }

    /**
     * Group routes with the given attributes
     *
     * @param array $attributes
     * @param Closure $routes
     * @return self
     */
    public function group(array $attributes, Closure $routes): self
    {
        // Backup current properties
        $oldName = $this->name;
        $oldMiddleware = $this->middleware;
        $oldNamespace = $this->namespace;
        $oldPrefix = $this->prefix;
        $oldDomain = $this->domain;

        $this->name = null;

        // Set middleware for the group
        if (isset($attributes[GroupAttributes::MIDDLEWARE])) {
            if (is_array($attributes[GroupAttributes::MIDDLEWARE]) == false) {
                $attributes[GroupAttributes::MIDDLEWARE] = [$attributes[GroupAttributes::MIDDLEWARE]];
            }

            $this->middleware = array_merge($attributes[GroupAttributes::MIDDLEWARE], $this->middleware);
        }

        // Set namespace for the group
        if (isset($attributes[GroupAttributes::NAMESPACE])) {
            $this->namespace = $attributes[GroupAttributes::NAMESPACE];
        }

        // Set prefix for the group
        if (isset($attributes[GroupAttributes::PREFIX])) {
            $this->prefix = $this->prefix . $attributes[GroupAttributes::PREFIX];
        }

        // Set domain for the group
        if (isset($attributes[GroupAttributes::DOMAIN])) {
            $this->domain = $attributes[GroupAttributes::DOMAIN];
        }

        // Run the group body closure
        call_user_func($routes, $this);

        // Restore properties
        $this->name = $oldName;
        $this->domain = $oldDomain;
        $this->prefix = $oldPrefix;
        $this->middleware = $oldMiddleware;
        $this->namespace = $oldNamespace;

        return $this;
    }

    /**
     * Map a controller to a route and set basic attributes
     *
     * @param string $method
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function map(
        ?string $method,
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        $name = $name ?: $this->name;
        $uri = $this->prefix . $route;
        $middleware = is_array($middleware) ? $middleware : [$middleware];

        if (is_string($controller) && is_callable($controller) == false) {
            $controller = $this->namespace . "\\" . $controller;
        }

        $route = new Route(
            $name,
            $uri,
            $method,
            $controller,
            array_merge($this->middleware, $middleware),
            $domain ?: $this->domain
        );

        $this->routes[] = $route;

        if ($name) {
            $this->names[$name] = $route;
            $this->name = null;
        }

        return $this;
    }

    /**
     * Dispatch routes and run the application
     *
     * @return self
     * @throws RouteNotFoundException
     * @throws InvalidControllerException
     * @throws InvalidMiddlewareException
     * @throws Throwable (the controller might throw any kind of exception)
     */
    public function dispatch(): self
    {
        $this->prepare();

        $method = $this->request->getMethod();
        $domain = $this->request->getUri()->getHost();
        $uri = $this->request->getUri()->getPath();

        sort($this->routes, SORT_DESC);

        foreach ($this->routes as $route) {
            $parameters = [];

            if (
                $this->compareMethod($route->getMethod(), $method) &&
                $this->compareDomain($route->getDomain(), $domain) &&
                $this->compareUri($route->getUri(), $uri, $parameters)
            ) {
                $this->currentRoute = $route;

                $this->publisher->publish($this->run($route, $parameters));

                return $this;
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Check if given request method matches given route method
     *
     * @param string|null $routeMethod
     * @param string $requestMethod
     * @return bool
     */
    private function compareMethod(?string $routeMethod, string $requestMethod): bool
    {
        return $routeMethod == null || $routeMethod == $requestMethod;
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
        return $routeDomain == null || preg_match('@^' . $routeDomain . '$@', $requestDomain);
    }

    /**
     * Check if given request uri matches given uri method
     *
     * @param string $routeUri
     * @param string $requestUri
     * @param array $parameters
     * @return bool
     */
    private function compareUri(string $routeUri, string $requestUri, array &$parameters): bool
    {
        $pattern = '@^' . $this->regexUri($routeUri) . '$@';

        return preg_match($pattern, $requestUri, $parameters);
    }

    /**
     * Run the controller of the given route
     *
     * @param Route $route
     * @param array $parameters
     * @return ResponseInterface|mixed|null
     * @throws InvalidControllerException
     * @throws InvalidMiddlewareException
     * @throws Throwable
     */
    private function run(Route $route, array $parameters)
    {
        $controller = $route->getController();

        if (count($middleware = $route->getMiddleware()) > 0) {
            $controllerRunner = function (ServerRequest $request) use ($controller, $parameters) {
                return $this->runController($controller, $parameters, $request);
            };

            return $this->runControllerThroughMiddleware($middleware, $this->request, $controllerRunner);
        }

        return $this->runController($controller, $parameters, $this->request);
    }

    /**
     * Run the controller through the middleware (list)
     *
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param ServerRequestInterface $request
     * @param Closure $controllerRunner
     * @param int $i
     * @return ResponseInterface|mixed|null
     * @throws InvalidMiddlewareException
     */
    private function runControllerThroughMiddleware(
        array $middleware,
        ServerRequestInterface $request,
        Closure $controllerRunner,
        $i = 0
    )
    {
        if (isset($middleware[$i + 1])) {
            $next = function (ServerRequestInterface $request) use ($middleware, $controllerRunner, $i) {
                return $this->runControllerThroughMiddleware($middleware, $request, $controllerRunner, $i + 1);
            };
        } else {
            $next = $controllerRunner;
        }

        if (is_callable($middleware[$i])) {
            return $middleware[$i]($request, $next);
        }

        if (is_subclass_of($middleware[$i], Middleware::class)) {
            if (is_string($middleware[$i])) {
                $middleware[$i] = new $middleware[$i];
            }

            return $middleware[$i]->handle($request, $next);
        }

        throw new InvalidMiddlewareException('Invalid middleware for route: ' . $this->currentRoute);
    }

    /**
     * Run the controller
     *
     * @param Closure|callable|string $controller
     * @param array $parameters
     * @param ServerRequestInterface $request
     * @return ResponseInterface|mixed|null
     * @throws InvalidControllerException
     */
    private function runController($controller, array $parameters, ServerRequestInterface $request)
    {
        try {
            if (is_string($controller) && strpos($controller, '@')) {
                list($className, $methodName) = explode('@', $controller);

                if (class_exists($className) == false) {
                    throw new InvalidControllerException("Controller class `$controller` not found.");
                }

                $classObject = new $className();

                if (method_exists($classObject, $methodName) == false) {
                    throw new InvalidControllerException("Controller method `$methodName` not found.");
                }

                $parameters = $this->arrangeMethodParameters($className, $methodName, $parameters, $request);

                $controller = [$classObject, $methodName];
            } elseif (is_callable($controller)) {
                $parameters = $this->arrangeFunctionParameters($controller, $parameters, $request);
            } else {
                throw new InvalidControllerException('Invalid controller: ' . $controller);
            }

            return call_user_func_array($controller, $parameters);
        } catch (ReflectionException $e) {
            throw new InvalidControllerException('Reflection error', 0, $e);
        }
    }

    /**
     * Arrange parameters for given function
     *
     * @param Closure|callable $function
     * @param array $parameters
     * @param ServerRequestInterface $request
     * @return array
     * @throws ReflectionException
     */
    private function arrangeFunctionParameters($function, array $parameters, ServerRequestInterface $request): array
    {
        return $this->arrangeParameters(new ReflectionFunction($function), $parameters, $request);
    }

    /**
     * Arrange parameters for given method
     *
     * @param string $class
     * @param string $method
     * @param array $parameters
     * @param ServerRequestInterface $request
     * @return array
     * @throws ReflectionException
     */
    private function arrangeMethodParameters(
        string $class,
        string $method,
        array $parameters,
        ServerRequestInterface $request
    ): array
    {
        return $this->arrangeParameters(new ReflectionMethod($class, $method), $parameters, $request);
    }

    /**
     * Arrange parameters for given method/function
     *
     * @param ReflectionFunctionAbstract $reflection
     * @param array $parameters
     * @param ServerRequestInterface $request
     * @return array
     */
    private function arrangeParameters(
        ReflectionFunctionAbstract $reflection,
        array $parameters,
        ServerRequestInterface $request
    ): array
    {
        return array_map(
            function (ReflectionParameter $parameter) use ($parameters, $request) {
                if (isset($parameters[$parameter->getName()])) {
                    return $parameters[$parameter->getName()];
                }

                if (
                    ($parameter->getType() && $parameter->getType()->getName() == ServerRequestInterface::class) ||
                    ($parameter->getType() && $parameter->getType()->getName() == ServerRequest::class) ||
                    ($parameter->getName() == 'request')
                ) {
                    return $request;
                }

                if (
                    ($parameter->getType() && $parameter->getType()->getName() == Router::class) ||
                    ($parameter->getName() == 'router')
                ) {
                    return $this;
                }

                if ($parameter->isOptional()) {
                    return $parameter->getDefaultValue();
                }

                return null;
            },

            $reflection->getParameters()
        );
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
     * Map a controller to given route for all the http methods
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function any(
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        return $this->map(null, $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map a controller to given GET route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function get(
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        return $this->map(HttpMethods::GET, $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map a controller to given POST route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function post(
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        return $this->map(HttpMethods::POST, $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map a controller to given PUT route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function put(
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        return $this->map(HttpMethods::PUT, $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map a controller to given PATCH route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function patch(
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        return $this->map(HttpMethods::PATCH, $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map a controller to given DELETE route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param string|callable|Closure|Middleware|string[]|callable[]|Closure[]|Middleware[] $middleware
     * @param string|null $domain
     * @param string|null $name
     * @return self
     */
    public function delete(
        string $route,
        $controller,
        $middleware = [],
        ?string $domain = null,
        ?string $name = null
    ): self
    {
        return $this->map(HttpMethods::DELETE, $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Use given name for the next route mapping
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
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
     * @param string $routeName
     * @param string[] $parameters
     * @return string
     * @throws UndefinedRouteException
     */
    public function url(string $routeName, array $parameters = []): string
    {
        if (isset($this->names[$routeName]) == false) {
            throw new UndefinedRouteException("There is no route with name `$routeName`.");
        }

        $uri = $this->names[$routeName]->getUri();

        foreach ($parameters as $name => $value) {
            $uri = preg_replace('/\??{' . $name . '\??}/', $value, $uri);
        }

        $uri = preg_replace('/{[^}]+\?}/', '', $uri);
        $uri = str_replace('/?', '', $uri);

        return $uri;
    }

    /**
     * @return Route|null
     */
    public function currentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    /**
     * Prepare router to dispatch routes
     */
    private function prepare(): void
    {
        $this->request = $this->request ?: ServerRequestFactory::fromGlobals();
        $this->publisher = $this->publisher ?: new HttpPublisher();
    }

    /**
     * Get current http request instance
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Set my own http request instance
     *
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
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
