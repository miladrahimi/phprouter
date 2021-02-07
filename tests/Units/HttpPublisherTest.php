<?php

namespace MiladRahimi\PhpRouter\Tests\Units;

use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class HttpPublisherTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_publish_a_string_response()
    {
        ob_start();

        $router = Router::create();
        $router->get('/', function () {
            return 'Hello!';
        });
        $router->dispatch();

        $this->assertEquals('Hello!', ob_get_clean());
    }

    /**
     * @throws Throwable
     */
    public function test_publish_a_empty_response()
    {
        ob_start();

        $router = Router::create();
        $router->get('/', function () {
            //
        });
        $router->dispatch();

        $this->assertEmpty(ob_get_clean());
    }

    /**
     * @throws Throwable
     */
    public function test_publish_a_array_response()
    {
        ob_start();

        $router = Router::create();
        $router->get('/', function () {
            return ['a', 'b', 'c'];
        });
        $router->dispatch();

        $this->assertEquals('["a","b","c"]', ob_get_clean());
    }

    /**
     * @throws Throwable
     */
    public function test_publish_a_standard_response()
    {
        ob_start();

        $router = Router::create();
        $router->get('/', function () {
            return new JsonResponse(['error' => 'failed'], 400);
        });

        $router->dispatch();

        $this->assertEquals('{"error":"failed"}', ob_get_clean());
    }
}
