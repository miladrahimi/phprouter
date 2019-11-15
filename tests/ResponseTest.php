<?php

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
        $router = $this->router()
            ->get('/', function () {
                return new EmptyResponse(204);
            })
            ->dispatch();

        $this->assertEquals(204, $this->status($router));
    }

    /**
     * @throws Throwable
     */
    public function test_html_response_with_code_200()
    {
        $router = $this->router()
            ->get('/', function () {
                return new HtmlResponse('<html lang="fa"></html>', 200);
            })
            ->dispatch();

        $this->assertEquals(200, $this->status($router));
        $this->assertEquals('<html lang="fa"></html>', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_json_response_with_code_201()
    {
        $router = $this->router()
            ->get('/', function () {
                return new JsonResponse(['a' => 'x', 'b' => 'y'], 201);
            })
            ->dispatch();

        $this->assertEquals(201, $this->status($router));
        $this->assertEquals(json_encode(['a' => 'x', 'b' => 'y']), $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_text_response_with_code_203()
    {
        $router = $this->router()
            ->get('/', function () {
                return new TextResponse('Content', 203);
            })
            ->dispatch();

        $this->assertEquals(203, $this->status($router));
        $this->assertEquals('Content', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_redirect_response_with_code_203()
    {
        $router = $this->router()
            ->get('/', function () {
                return new RedirectResponse('https://miladrahimi.com');
            })
            ->dispatch();

        $this->assertEquals(302, $this->status($router));
        $this->assertEquals('', $this->output($router));
        $this->assertContains('location: https://miladrahimi.com', $this->publisher($router)->headerLines);
    }
}
