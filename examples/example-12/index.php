<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

$router = new Router();

$router
    ->get('/html/1', function () {
        return '<html>This is an HTML response</html>';
    })
    ->get('/html/2', function () {
        return new HtmlResponse('<html>This is also an HTML response</html>', 200);
    })
    ->get('/json', function () {
        return new JsonResponse(['error' => 'Unauthorized!'], 401);
    })
    ->get('/text', function () {
        return new TextResponse('This is a plain text...');
    })
    ->get('/empty', function () {
        return new EmptyResponse();
    });

$router->dispatch();
