<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class PatternsTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_a_single_digit_pattern()
    {
        $id = random_int(1, 9);
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->pattern('id', '[0-9]');
        $router->get('/products/{id}', function ($id) {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals($id, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_single_digit_pattern_given_more()
    {
        $id = random_int(10, 100);
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->pattern('id', '[0-9]');
        $router->get('/products/{id}', function ($id) {
            return $id;
        });

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_multi_digits_pattern()
    {
        $id = random_int(10, 100);
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->pattern('id', '[0-9]+');
        $router->get('/products/{id}', function ($id) {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals($id, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_multi_digits_pattern_given_string()
    {
        $this->mockRequest('GET', "http://example.com/products/string");

        $router = $this->router();
        $router->pattern('id', '[0-9]+');
        $router->get('/products/{id}', function ($id) {
            return $id;
        });

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_alphanumeric_pattern()
    {
        $id = 'abc123xyz';
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->pattern('id', '[0-9a-z]+');
        $router->get('/products/{id}', function ($id) {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals($id, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_alphanumeric_pattern_given_invalid()
    {
        $id = 'abc$$$';
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->pattern('id', '[0-9a-z]+');
        $router->get('/products/{id}', function ($id) {
            return $id;
        });

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch();
    }
}
