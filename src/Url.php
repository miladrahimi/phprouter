<?php

namespace MiladRahimi\PhpRouter;

use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Routes\Store;

class Url
{
    /**
     * @var Store
     */
    private $store;

    /**
     * Url constructor.
     *
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
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
        if (!($route = $this->store->findByName($routeName))) {
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
