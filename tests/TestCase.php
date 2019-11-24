<?php

namespace MiladRahimi\PhpRouter\Tests;

use Closure;
use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Services\Publisher;
use MiladRahimi\PhpRouter\Tests\Testing\TestPublisher;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockRequest(HttpMethods::GET, 'http://example.com/');
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
     * @param string $prefix
     * @param string $namespace
     * @return Router
     */
    protected function router(string $prefix = '', string $namespace = ''): Router
    {
        $router = new Router($prefix, $namespace);
        $router->setPublisher(new TestPublisher());

        return $router;
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
        return $router->getPublisher()->output;
    }

    /**
     * Get the given router publisher.
     *
     * @param Router $router
     * @return TestPublisher|Publisher
     */
    protected function publisher(Router $router): TestPublisher
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
