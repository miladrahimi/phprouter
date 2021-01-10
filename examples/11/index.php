<?php

require('../../vendor/autoload.php');

use Laminas\Diactoros\Response\RedirectResponse;
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;

$router = Router::create();

$router->get('/html/1', function () {
    return '<html>This is an HTML response</html>';
});
$router->get('/html/2', function () {
    return new HtmlResponse('<html>This is also an HTML response</html>', 200);
});
$router->get('/json', function () {
    return new JsonResponse(['error' => 'Unauthorized!'], 401);
});
$router->get('/text', function () {
    return new TextResponse('This is a plain text...');
});
$router->get('/empty', function () {
    return new EmptyResponse(204);
});
$router->get('/redirect', function () {
    return new RedirectResponse('https://miladrahimi.com');
});

$router->dispatch();
