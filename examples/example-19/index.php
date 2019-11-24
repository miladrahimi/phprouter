<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->name('home')->get('/', function (Router $router) {
    return new JsonResponse([
        'current_page_name'   => $router->currentRoute()->getName(),   /* Result: home  */
        'current_page_uri'    => $router->currentRoute()->getUri(),    /* Result: /     */
        'current_page_method' => $router->currentRoute()->getMethod(), /* Result: GET   */
        'current_page_domain' => $router->currentRoute()->getDomain(), /* Result: null  */
    ]);
});

$router->dispatch();
