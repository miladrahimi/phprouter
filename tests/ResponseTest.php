<?php

namespace MiladRahimi\PhpRouter\Tests;

use Throwable;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;

/**
 * Class ResponseTest
 *
 * @package MiladRahimi\PhpRouter\Tests
 */
class ResponseTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_empty_response_with_code_204()
    {
        $router = $this->router();

        $router->get('/', function () {
            return new EmptyResponse(204);
        });

        $router->dispatch();

        $this->assertEquals(204, $this->publisherOf($router)->responseCode);
    }

    /**
     * @throws Throwable
     */
    public function test_html_response_with_code_200()
    {
        $router = $this->router();

        $router->get('/', function () {
            return new HtmlResponse('<html lang="fa"></html>', 200);
        });

        $router->dispatch();

        $this->assertEquals(200, $this->publisherOf($router)->responseCode);
        $this->assertEquals('<html lang="fa"></html>', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_json_response_with_code_201()
    {
        $router = $this->router();

        $router->get('/', function () {
            return new JsonResponse(['a' => 'x', 'b' => 'y'], 201);
        });

        $router->dispatch();

        $this->assertEquals(201, $this->publisherOf($router)->responseCode);
        $this->assertEquals(json_encode(['a' => 'x', 'b' => 'y']), $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_text_response_with_code_203()
    {
        $router = $this->router();

        $router->get('/', function () {
            return new TextResponse('Content', 203);
        });

        $router->dispatch();

        $this->assertEquals(203, $this->publisherOf($router)->responseCode);
        $this->assertEquals('Content', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_redirect_response_with_code_203()
    {
        $router = $this->router();

        $router->get('/', function () {
            return new RedirectResponse('https://miladrahimi.com');
        });

        $router->dispatch();

        $this->assertEquals(302, $this->publisherOf($router)->responseCode);
        $this->assertEquals('', $this->outputOf($router));
        $this->assertContains('location: https://miladrahimi.com', $this->publisherOf($router)->headerLines);
    }
}
