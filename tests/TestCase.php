<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Publisher\Publisher;
use MiladRahimi\PhpRouter\Tests\Common\TrapPublisher;
use PHPUnit\Framework\TestCase as BaseTestCase;

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
        $router = Router::create();
        $router->setPublisher(new  TrapPublisher());

        return $router;
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
