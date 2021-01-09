<?php

namespace MiladRahimi\PhpRouter\Tests;

use Closure;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Routing\Repository;
use MiladRahimi\PhpRouter\Services\HttpPublisher;
use MiladRahimi\PhpRouter\Services\Publisher;
use MiladRahimi\PhpRouter\Tests\Common\TrapPublisher;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;

class TestCase extends BaseTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest('GET', 'http://example.com/');
    }

    /**
     * Manipulate the current HTTP request ($_SERVER)
     *
     * @param string $method
     * @param string $url
     */
    protected function mockRequest(string $method, string $url): void
    {
        $urlParts = parse_url($url);

        $_SERVER['SERVER_NAME'] = $urlParts['host'];
        $_SERVER['REQUEST_URI'] = $urlParts['path'] . '?' . ($urlParts['query'] ?? '');
        $_SERVER['REQUEST_METHOD'] = $method;
    }

    /**
     * Get a router instance for testing purposes
     *
     * @return Router
     * @throws ContainerException
     */
    protected function router(): Router
    {
        $container = new Container();
        $container->singleton(Container::class, $container);
        $container->singleton(ContainerInterface::class, $container);
        $container->singleton(Repository::class, new Repository());
        $container->singleton(Publisher::class, TrapPublisher::class);

        return $container->instantiate(Router::class);
    }

    /**
     * Get a sample controller that returns an 'OK' string
     *
     * @return Closure
     */
    protected function OkController(): Closure
    {
        return function () {
            return 'OK';
        };
    }

    /**
     * Get the generated output of the dispatched route of the given router
     *
     * @param Router $router
     * @return string
     */
    protected function output(Router $router)
    {
        return $this->publisher($router)->output;
    }

    /**
     * Get the given router publisher.
     *
     * @param Router $router
     * @return TrapPublisher|Publisher
     */
    protected function publisher(Router $router): TrapPublisher
    {
        return $router->getPublisher();
    }

    /**
     * Get the HTTP status code of the dispatched route of the given router
     *
     * @param Router $router
     * @return int
     */
    protected function status(Router $router): int
    {
        return $this->publisher($router)->responseCode;
    }
}
