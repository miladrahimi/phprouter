<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;

$router = Router::create();

$router->get('/test', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri()->getPath(),
        'body' => $request->getBody()->getContents(),
        'parsedBody' => $request->getParsedBody(),
        'headers' => $request->getHeaders(),
        'queryParameters' => $request->getQueryParams(),
        'attributes' => $request->getAttributes(),
    ]);
});

$router->dispatch();
