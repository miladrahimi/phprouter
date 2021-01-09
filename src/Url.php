<?php

namespace MiladRahimi\PhpRouter;

use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Routing\Repository;

class Url
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * Url constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate URL for given route name
     *
     * @param string $routeName
     * @param string[] $parameters
     * @return string
     * @throws UndefinedRouteException
     */
    public function make(string $routeName, array $parameters = []): string
    {
        if (!($route = $this->repository->findByName($routeName))) {
            throw new UndefinedRouteException("There is no route named `$routeName`.");
        }

        $uri = $route->getPath();

        foreach ($parameters as $name => $value) {
            $uri = preg_replace('/\??{' . $name . '\??}/', $value, $uri);
        }

        $uri = preg_replace('/{[^}]+\?}/', '', $uri);
        $uri = str_replace('/?', '', $uri);

        return $uri;
    }
}
