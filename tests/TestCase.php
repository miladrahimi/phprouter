<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 01:57
 */

namespace MiladRahimi\Router\Tests;

use MiladRahimi\Router\Enums\HttpMethods;
use MiladRahimi\Router\Router;
use MiladRahimi\Router\Services\HeaderExposer;
use MiladRahimi\Router\Tests\Classes\SampleController;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockRequest(HttpMethods::GET, 'http://example.com/');
    }

    /**
     * Mock http server request ($_SERVER)
     *
     * @param string $method
     * @param string $url
     */
    protected function mockRequest(string $method, string $url)
    {
        $urlParts = parse_url($url);

        $_SERVER['SERVER_NAME'] = $urlParts['scheme'] . '://' . $urlParts['host'];
        $_SERVER['REQUEST_URI'] = ($urlParts['path'] ?? '/') . '?' . ($urlParts['query'] ?? '');
        $_SERVER['REQUEST_METHOD'] = $method;
    }


    /**
     * Mock header exposer service
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|HeaderExposer
     */
    protected function mockHeaderExposer()
    {
        return $this->getMockBuilder(HeaderExposer::class)
            ->setMethods(['addHeaderLine'])
            ->getMock();
    }

    /**
     * Get router instance with mocked properties
     *
     * @return Router
     */
    protected function createRouterWithMockedProperties(): Router
    {
        $router = new Router();
        $router->setHeaderExposer($this->mockHeaderExposer());

        return $router;
    }

    /**
     * Get the simplest controller
     *
     * @return string
     */
    protected function simpleController(): string
    {
        return SampleController::class . '@getNoParameter';
    }
}
