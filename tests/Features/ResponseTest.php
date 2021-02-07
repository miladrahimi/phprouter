<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;

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

        $this->assertEquals(204, $this->status($router));
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

        $this->assertEquals(200, $this->status($router));
        $this->assertEquals('<html lang="fa"></html>', $this->output($router));
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

        $this->assertEquals(201, $this->status($router));
        $this->assertEquals(json_encode(['a' => 'x', 'b' => 'y']), $this->output($router));
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

        $this->assertEquals(203, $this->status($router));
        $this->assertEquals('Content', $this->output($router));
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

        $this->assertEquals(302, $this->status($router));
        $this->assertEquals('', $this->output($router));
        $this->assertContains('location: https://miladrahimi.com', $this->publisher($router)->headerLines);
    }
}
