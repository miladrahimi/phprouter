<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/13/2018 AD
 * Time: 18:21
 */

namespace MiladRahimi\PhpRouter\Tests;

use Throwable;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;

class ResponseTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_empty_response_with_code_204()
    {
        $router = $this->createRouter();

        $router->map('GET', '/', function () {
            return new EmptyResponse(204);
        });

        $router->dispatch();

        $this->assertEquals(204, $this->getPublisher($router)->responseCode);
    }

    /**
     * @throws Throwable
     */
    public function test_html_response_with_code_200()
    {
        $router = $this->createRouter();

        $router->map('GET', '/', function () {
            return new HtmlResponse('<html></html>', 200);
        });

        $router->dispatch();

        $this->assertEquals(200, $this->getPublisher($router)->responseCode);
        $this->assertEquals('<html></html>', $this->getPublisher($router)->output);
    }

    /**
     * @throws Throwable
     */
    public function test_json_response_with_code_201()
    {
        $router = $this->createRouter();

        $router->map('GET', '/', function () {
            return new JsonResponse(['a' => 'x', 'b' => 'y'], 201);
        });

        $router->dispatch();

        $this->assertEquals(201, $this->getPublisher($router)->responseCode);
        $this->assertEquals(json_encode(['a' => 'x', 'b' => 'y']), $this->getPublisher($router)->output);
    }

    /**
     * @throws Throwable
     */
    public function test_text_response_with_code_203()
    {
        $router = $this->createRouter();

        $router->map('GET', '/', function () {
            return new TextResponse('Content', 203);
        });

        $router->dispatch();

        $this->assertEquals(203, $this->getPublisher($router)->responseCode);
        $this->assertEquals('Content', $this->getPublisher($router)->output);
    }

    /**
     * @throws Throwable
     */
    public function test_redirect_response_with_code_203()
    {
        $router = $this->createRouter();

        $router->map('GET', '/', function () {
            return new RedirectResponse('https://miladrahimi.com');
        });

        $router->dispatch();

        $this->assertEquals(302, $this->getPublisher($router)->responseCode);
        $this->assertEquals('', $this->getPublisher($router)->output);
        $this->assertContains('location: https://miladrahimi.com', $this->getPublisher($router)->headerLines);
    }
}
