<?php

namespace MiladRahimi\PhpRouter\Dispatching;

use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Routing\Route;
use MiladRahimi\PhpRouter\Routing\Repository;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Matcher
 * It finds an appropriate route for HTTP requests
 *
 * @package MiladRahimi\PhpRouter\Dispatching
 */
class Matcher
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * Constructor
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Find the right route for the given request and defined patterns
     *
     * @param ServerRequestInterface $request
     * @param string[] $patterns
     * @return Route
     * @throws RouteNotFoundException
     */
    public function find(ServerRequestInterface $request, array $patterns)
    {
        foreach ($this->repository->findByMethod($request->getMethod()) as $route) {
            $parameters = [];

            if ($this->compare($route, $request, $parameters, $patterns)) {
                $route->setUri($request->getUri()->getPath());
                $route->setParameters($this->pruneRouteParameters($parameters));

                return $route;
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * Prune route parameters (remove unnecessary parameters)
     *
     * @param string[] $parameters
     * @return string[]
     * @noinspection PhpUnusedParameterInspection
     */
    private function pruneRouteParameters(array $parameters): array
    {
        return array_filter($parameters, function ($value, $name) {
            return is_numeric($name) === false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Compare the route with the given HTTP request
     *
     * @param Route $route
     * @param ServerRequestInterface $request
     * @param string[] $parameters
     * @param string[] $patterns
     * @return bool
     */
    private function compare(Route $route, ServerRequestInterface $request, array &$parameters, array $patterns): bool
    {
        return (
            $this->compareDomain($route->getDomain(), $request->getUri()->getHost()) &&
            $this->compareUri($route->getPath(), $request->getUri()->getPath(), $parameters, $patterns)
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
     * @param string[] $parameters
     * @param string[] $patterns
     * @return bool
     */
    private function compareUri(string $path, string $uri, array &$parameters, array $patterns): bool
    {
        return preg_match('@^' . $this->regexUri($path, $patterns) . '$@', $uri, $parameters);
    }

    /**
     * Convert route to regex
     *
     * @param string $path
     * @param string[] $patterns
     * @return string
     */
    private function regexUri(string $path, array $patterns): string
    {
        return preg_replace_callback('@{([^}]+)}@', function (array $match) use ($patterns) {
            return $this->regexParameter($match[1], $patterns);
        }, $path);
    }

    /**
     * Convert route parameter to regex
     *
     * @param string $name
     * @param array $patterns
     * @return string
     */
    private function regexParameter(string $name, array $patterns): string
    {
        if ($name[-1] == '?') {
            $name = substr($name, 0, -1);
            $suffix = '?';
        } else {
            $suffix = '';
        }

        $pattern = $patterns[$name] ?? '[^/]+';

        return '(?<' . $name . '>' . $pattern . ')' . $suffix;
    }
}
