<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/5/2018 AD
 * Time: 12:23
 */

namespace MiladRahimi\PhpRouter;

use Closure;
use MiladRahimi\PhpRouter\Enums\RouteAttributes;
use MiladRahimi\PhpRouter\Exceptions\InvalidControllerException;
use MiladRahimi\PhpRouter\Exceptions\InvalidMiddlewareException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Services\Publisher;
use MiladRahimi\PhpRouter\Services\PublisherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class Router
{
    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $routeNames = [];

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $currentName = null;

    /**
     * @var array
     */
    private $currentMiddleware = [];

    /**
     * @var string
     */
    private $currentPrefix = '';

    /**
     * @var string|null
     */
    private $currentDomain = null;

    /**
     * @var array|null
     */
    private $currentRoute = null;

    /**
     * @var string|null
     */
    private $currentRouteName = null;

    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->initializeRequestAndResponse();
        $this->publisher = new Publisher();
    }

    /**
     * Group routes with given properties
     *
     * @param array $attributes
     * @param Closure $routes
     */
    public function group($attributes = [], Closure $routes)
    {
        // Backup previous group properties
        $oldMiddleware = $this->currentMiddleware;
        $oldPrefix = $this->currentPrefix;
        $oldDomain = $this->currentDomain;
        $oldName = $this->currentName;

        // There shouldn't be any name!
        $this->currentName = null;

        // Set given middleware for the group
        if (isset($attributes[RouteAttributes::MIDDLEWARE])) {
            if (is_array($attributes[RouteAttributes::MIDDLEWARE]) == false) {
                $attributes[RouteAttributes::MIDDLEWARE] = [$attributes[RouteAttributes::MIDDLEWARE]];
            }

            $this->currentMiddleware = array_merge($attributes[RouteAttributes::MIDDLEWARE], $this->currentMiddleware);
        }

        // Set given prefix for the group
        if (isset($attributes[RouteAttributes::PREFIX])) {
            $this->currentPrefix = $attributes[RouteAttributes::PREFIX] . $this->currentPrefix;
        }

        // Set given domain for the group
        if (isset($attributes[RouteAttributes::DOMAIN])) {
            $this->currentDomain = $attributes[RouteAttributes::DOMAIN];
        }

        // Run group body closure
        call_user_func($routes, $this);

        // Revert to previous group properties using the backups
        $this->currentName = $oldName;
        $this->currentDomain = $oldDomain;
        $this->currentPrefix = $oldPrefix;
        $this->currentMiddleware = $oldMiddleware;
    }

    /**
     * Map given controller to given route
     *
     * @param string $method
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function map(
        string $method,
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $route = $this->currentPrefix . $route;
        $middleware = is_array($middleware) ? $middleware : [$middleware];

        // Add the route to route list
        $this->routes[strtoupper($method)][$route] = [
            RouteAttributes::METHOD => $method,
            RouteAttributes::URI => $route,
            RouteAttributes::CONTROLLER => $controller,
            RouteAttributes::MIDDLEWARE => array_merge($this->currentMiddleware, $middleware),
            RouteAttributes::DOMAIN => $domain ?: $this->currentDomain,
        ];

        // Add the route to named route list
        if ($name || $this->currentName) {
            $this->routeNames[$name ?: $this->currentName] = [
                RouteAttributes::URI => $route,
                RouteAttributes::METHOD => $method,
            ];

            // Names are disposable!
            $this->currentName = null;
        }
    }

    /**
     * Dispatch routes and run the application
     *
     * @throws InvalidControllerException
     * @throws InvalidMiddlewareException
     * @throws RouteNotFoundException
     * @throws Throwable
     */
    public function dispatch()
    {
        $method = $this->serverRequest->getMethod();
        $scheme = $this->serverRequest->getUri()->getScheme();
        $domain = substr($this->serverRequest->getUri()->getHost(), strlen($scheme . '://'));
        $uri = $this->serverRequest->getUri()->getPath();

        $routes = array_merge($this->routes[$method] ?? [], $this->routes['*'] ?? []);

        foreach ($routes as $route => $routeAttributes) {
            $routeParameters = [];

            if ($this->matchRoute($route, $uri, $routeParameters)) {
                $domainPattern = $routeAttributes[RouteAttributes::DOMAIN];
                if ($domainPattern !== null && $this->match($domainPattern, $domain) == false) {
                    continue;
                }

                $this->currentRoute = [
                    RouteAttributes::METHOD => $routeAttributes[RouteAttributes::METHOD],
                    RouteAttributes::URI => $routeAttributes[RouteAttributes::URI],
                ];

                $this->publish($this->run($routeAttributes, $routeParameters));

                return;
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Run the application
     *
     * @param array $routeAttributes
     * @param array $routeParameters
     * @return mixed|ResponseInterface
     * @throws InvalidControllerException
     * @throws InvalidMiddlewareException
     * @throws Throwable
     */
    private function run(array $routeAttributes, array $routeParameters)
    {
        $controller = $routeAttributes[RouteAttributes::CONTROLLER];

        if (count($middleware = $routeAttributes[RouteAttributes::MIDDLEWARE]) > 0) {
            $controllerRunner = function () use ($controller, $routeParameters) {
                return $this->runController($controller, $routeParameters);
            };

            return $this->runControllerThroughMiddleware($middleware, $controllerRunner);
        } else {
            return $this->runController($controller, $routeParameters);
        }
    }

    /**
     * Initialize http request and response
     */
    private function initializeRequestAndResponse()
    {
        $this->response = new Response();
        $this->serverRequest = ServerRequestFactory::fromGlobals();

        foreach (array_merge($_GET ?? [], $_POST ?? []) as $name => $value) {
            $this->serverRequest = $this->serverRequest->withAttribute($name, $value);
        }

        if (is_array($bodyParameters = json_decode(file_get_contents('php://input'), true))) {
            $this->serverRequest = $this->serverRequest->withParsedBody($bodyParameters);

            foreach ($bodyParameters as $name => $value) {
                $this->serverRequest = $this->serverRequest->withAttribute($name, $value);
            }
        }
    }

    /**
     * Run the controller through the middleware
     *
     * @param Middleware[] $middleware
     * @param Closure $controllerRunner
     * @param int $i
     * @return ResponseInterface|null
     * @throws InvalidMiddlewareException
     */
    private function runControllerThroughMiddleware(array $middleware, Closure $controllerRunner, $i = 0)
    {
        if (isset($middleware[$i + 1])) {
            $next = function () use ($middleware, $controllerRunner, $i) {
                return $this->runControllerThroughMiddleware($middleware, $controllerRunner, $i + 1);
            };
        } else {
            $next = $controllerRunner;
        }

        if (is_subclass_of($middleware[$i], Middleware::class) == false) {
            throw new InvalidMiddlewareException('Invalid middleware for route: ' . json_encode($this->currentRoute));
        }

        if (is_string($middleware[$i])) {
            $middleware[$i] = new $middleware[$i];
        }

        return $middleware[$i]->handle($this->serverRequest, $next);
    }

    /**
     * Run the controller
     *
     * @param Closure|callable|string $controller
     * @param array $parameters
     * @return ResponseInterface|null
     * @throws InvalidControllerException
     * @throws Throwable
     */
    private function runController($controller, array $parameters)
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

                $parameters = $this->arrangeMethodParameters($className, $methodName, $parameters);

                $controller = [$classObject, $methodName];
            } elseif (is_callable($controller)) {
                $parameters = $this->arrangeFunctionParameters($controller, $parameters);
            } else {
                throw new InvalidControllerException('Invalid controller: ' . $controller);
            }

            return call_user_func_array($controller, $parameters);
        } catch (ReflectionException $e) {
            throw new InvalidControllerException('', 0, $e);
        }
    }

    /**
     * Arrange parameters for given function
     *
     * @param Closure|callable $function
     * @param array $parameters
     * @return array
     * @throws ReflectionException
     */
    private function arrangeFunctionParameters($function, array $parameters)
    {
        return $this->arrangeParameters(new ReflectionFunction($function), $parameters);
    }

    /**
     * Arrange parameters for given method
     *
     * @param string $class
     * @param string $method
     * @param array $parameters
     * @return array
     * @throws ReflectionException
     */
    private function arrangeMethodParameters(string $class, string $method, array $parameters)
    {
        return $this->arrangeParameters(new ReflectionMethod($class, $method), $parameters);
    }

    /**
     * Arrange parameters for given method/function
     *
     * @param ReflectionFunctionAbstract $reflection
     * @param array $parameters
     * @return array
     */
    private function arrangeParameters(ReflectionFunctionAbstract $reflection, array $parameters)
    {
        return array_map(
            function (ReflectionParameter $parameter) use ($parameters) {
                if (isset($parameters[$parameter->getName()])) {
                    return $parameters[$parameter->getName()];
                }

                if (
                    ($parameter->getType() && $parameter->getType()->getName() == ServerRequestInterface::class) ||
                    $parameter->getName() == 'request'
                ) {
                    return $this->serverRequest;
                }

                if (
                    ($parameter->getType() && $parameter->getType()->getName() == ResponseInterface::class) ||
                    $parameter->getName() == 'response'
                ) {
                    return $this->response;
                }

                if ($parameter->getType() && $parameter->getType()->getName() == Router::class) {
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
     * Check if the route matches the uri and extract parameters if it does
     *
     * @param string $route
     * @param string $uri
     * @param array $parameters
     * @return bool
     */
    private function matchRoute(string $route, string $uri, array &$parameters): bool
    {
        $pattern = '@^' . $this->regexRoute($route) . '$@';

        return preg_match($pattern, $uri, $parameters);
    }

    /**
     * Convert route to regex
     *
     * @param string $route
     * @return string
     */
    private function regexRoute(string $route): string
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
        if ($name[mb_strlen($name) - 1] == '?') {
            $name = substr($name, 0, strlen($name) - 1);
            $suffix = '?';
        } else {
            $suffix = '';
        }

        $pattern = $this->parameters[$name] ?? '[^/]+';

        return '(?<' . $name . '>' . $pattern . ')' . $suffix;
    }

    /**
     * Check if subject matches pattern
     *
     * @param string $pattern
     * @param string $subject
     * @return bool
     */
    private function match(string $pattern, string $subject): bool
    {
        return preg_match('@^' . $pattern . '$@', $subject);
    }

    /**
     * Map given controller to all the methods
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function any(
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $this->map('*', $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map given controller to given GET route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function get(
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $this->map('GET', $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map given controller to given POST route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function post(
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $this->map('POST', $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map given controller to given PUT route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function put(
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $this->map('PUT', $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map given controller to given PATCH route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function patch(
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $this->map('PATCH', $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Map given controller to given DELETE route
     *
     * @param string $route
     * @param Closure|callable|string $controller
     * @param Middleware|string|Middleware[]|string[] $middleware
     * @param string|null $domain
     * @param string|null $name
     */
    public function delete(
        string $route,
        $controller,
        $middleware = [],
        string $domain = null,
        string $name = null
    ) {
        $this->map('DELETE', $route, $controller, $middleware, $domain, $name);
    }

    /**
     * Use given name for the next route mapping
     *
     * @param string $name
     * @return Router
     */
    public function useName(string $name): self
    {
        $this->currentName = $name;

        return $this;
    }

    /**
     * Define parameter pattern
     *
     * @param string $name
     * @param string $pattern
     * @return $this
     */
    public function defineParameter(string $name, string $pattern)
    {
        $this->parameters[$name] = $pattern;

        return $this;
    }

    /**
     * Check if the current execution belongs to given route or not
     *
     * @param string $routeName
     * @return bool
     */
    public function isRoute(string $routeName): bool
    {
        if (
            isset($this->routeNames[$routeName]) &&
            is_array($this->currentRoute) &&
            $this->currentRoute[RouteAttributes::METHOD] == $this->routeNames[$routeName][RouteAttributes::METHOD] &&
            $this->currentRoute[RouteAttributes::URI] == $this->routeNames[$routeName][RouteAttributes::URI]
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get current route name
     *
     * @return string|null
     */
    public function currentRouteName()
    {
        if ($this->currentRouteName !== null) {
            return $this->currentRouteName;
        }

        if (is_array($this->currentRoute) == false) {
            return null;
        }

        foreach ($this->routeNames as $name => $attributes) {
            if (
                $attributes[RouteAttributes::METHOD] == $this->currentRoute[RouteAttributes::METHOD] &&
                $attributes[RouteAttributes::URI] == $this->currentRoute[RouteAttributes::URI]
            ) {
                return $this->currentRouteName = $name;
            }
        }

        return null;
    }

    /**
     * Publish http response manually
     *
     * @param $httpResponse
     */
    public function publish($httpResponse)
    {
        $this->publisher->publish($httpResponse);
    }

    /**
     * Get current http request instance
     *
     * @return ServerRequestInterface
     */
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    /**
     * Set my own http request instance
     *
     * @param ServerRequestInterface $serverRequest
     */
    public function setServerRequest(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * Get current http response instance
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set my own http response instance
     *
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return PublisherInterface
     */
    public function getPublisher(): PublisherInterface
    {
        return $this->publisher;
    }

    /**
     * @param PublisherInterface $publisher
     */
    public function setPublisher(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }
}
