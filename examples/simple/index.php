<?php

use MiladRahimi\PhpRouter\Router;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\TextResponse;

require('../../vendor/autoload.php');

$router = new Router();

$router->get('/', function () {
    return new HtmlResponse('<p>Homepage!</p>');
});


$router->get('/search', function (\Zend\Diactoros\ServerRequest $request) {
    return new TextResponse(json_encode($request->getAttribute()));
});

$router->dispatch();
