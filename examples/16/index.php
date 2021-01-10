<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\Routing\Route;

$router = Router::create();

$router->get('/{id}', function (Route $route) {
    return new JsonResponse([
        'uri'    => $route->getUri(),            /* Result: "/1" */
        'name'   => $route->getName(),           /* Result: sample */
        'path'   => $route->getPath(),           /* Result: "/{id}" */
        'method' => $route->getMethod(),         /* Result: GET */
        'domain' => $route->getDomain(),         /* Result: null */
        'parameters' => $route->getParameters(), /* Result: {"id": "1"} */
        'middleware' => $route->getMiddleware(), /* Result: []  */
        'controller' => $route->getController(), /* Result: {}  */
    ]);
}, 'sample');

$router->dispatch();
