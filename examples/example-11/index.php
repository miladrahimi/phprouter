<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Examples\Samples\PostModel;
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->get('/', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
        'body' => $request->getBody(),
        'parsedBody' => $request->getParsedBody(),
        'headers' => $request->getHeaders(),
        'queryParameters' => $request->getQueryParams(),
        'attributes' => $request->getAttributes(),
    ]);
});

$router->post('/posts', function (ServerRequest $request) {
    $post = new PostModel();
    $post->title = $request->getQueryParams()['title'];
    $post->content = $request->getQueryParams()['content'];
    $post->save();

    return new EmptyResponse(201);
});

$router->dispatch();
