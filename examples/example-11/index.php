<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Examples\Shared\PostModel;
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;

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
