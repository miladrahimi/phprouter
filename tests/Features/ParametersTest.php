<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class ParametersTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_a_required_parameter()
    {
        $id = random_int(1, 100);
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->get('/products/{id}', function ($id) {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals($id, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_some_required_parameters()
    {
        $pid = random_int(1, 100);
        $cid = random_int(1, 100);
        $this->mockRequest('GET', "http://example.com/products/$pid/comments/$cid");

        $router = $this->router();
        $router->get('/products/{pid}/comments/{cid}', function ($pid, $cid) {
            return $pid . $cid;
        });
        $router->dispatch();

        $this->assertEquals($pid . $cid, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_provided_optional_parameter()
    {
        $id = random_int(1, 100);
        $this->mockRequest('GET', "http://example.com/products/$id");

        $router = $this->router();
        $router->get('/products/{id?}', function ($id = 'default') {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals($id, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_unprovided_optional_parameter()
    {
        $this->mockRequest('GET', "http://example.com/products/");

        $router = $this->router();
        $router->get('/products/{id?}', function ($id = 'default') {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals('default', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_unprovided_optional_parameter_and_slash()
    {
        $this->mockRequest('GET', "http://example.com/products");

        $router = $this->router();
        $router->get('/products/?{id?}', function ($id = 'default') {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals('default', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_some_optional_parameters()
    {
        $pid = random_int(1, 100);
        $cid = random_int(1, 100);
        $this->mockRequest('GET', "http://example.com/products/$pid/comments/$cid");

        $router = $this->router();
        $router->get('/products/{pid?}/comments/{cid?}', function ($pid, $cid) {
            return $pid . $cid;
        });
        $router->dispatch();

        $this->assertEquals($pid . $cid, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_mixin_parameters()
    {
        $pid = random_int(1, 100);
        $cid = random_int(1, 100);
        $this->mockRequest('GET', "http://example.com/products/$pid/comments/$cid");

        $router = $this->router();
        $router->get('/products/{pid}/comments/{cid?}', function ($pid, $cid = 'default') {
            return $pid . $cid;
        });
        $router->dispatch();

        $this->assertEquals($pid . $cid, $this->output($router));
    }
}