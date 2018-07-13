<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/13/2018 AD
 * Time: 18:33
 */

use MiladRahimi\Router\Exceptions\RouteNotFoundException;
use MiladRahimi\Router\Router;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

require './../vendor/autoload.php';

$router = new Router();

$router->get('/', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
        'body' => $request->getBody(),
        'headers' => $request->getHeaders(),
    ]);
});

$router->post('/', function () {
    $html = '<html>Object successfully created.</html>';

    return new HtmlResponse($html, 201);
});

$router->put('/{id}', function ($id) {
    $text = 'The entity with ' . $id . ' updated.';

    return new TextResponse($text);
});

$router->patch('/{id}', function () {
    return new EmptyResponse();
});

$router->get('/redirect', function () {
    return new RedirectResponse('https://miladrahimi.com');
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->publish(new EmptyResponse(404));
} catch (Throwable $e) {
    echo '<pre>' . print_r($e, true) . '</pre>';
}