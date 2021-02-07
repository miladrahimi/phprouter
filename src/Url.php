<?php

namespace MiladRahimi\PhpRouter;

use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Routing\Repository;

/**
 * Class Url
 * It generates URLs by name based on the defined routes
 *
 * @package MiladRahimi\PhpRouter
 */
class Url
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
     * Generate (make) URL by name based on the defined routes
     *
     * @param string $name
     * @param string[] $parameters
     * @return string
     * @throws UndefinedRouteException
     */
    public function make(string $name, array $parameters = []): string
    {
        if (!($route = $this->repository->findByName($name))) {
            throw new UndefinedRouteException("There is no route named `$name`.");
        }

        $uri = $route->getPath();

        foreach ($parameters as $key => $value) {
            $uri = preg_replace('/\??{' . $key . '\??}/', $value, $uri);
        }

        $uri = preg_replace('/{[^}]+\?}/', '', $uri);
        $uri = str_replace('/?', '', $uri);

        return $uri;
    }
}
