<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\JsonResponse;

$router = new Router();

$router->get('/', function (Router $router) {
    return new JsonResponse([
        'current_page_name'   => $router->currentRoute()->getName(),   /* Result: home  */
        'current_page_path'    => $router->currentRoute()->getPath(),    /* Result: /     */
        'current_page_method' => $router->currentRoute()->getMethod(), /* Result: GET   */
        'current_page_domain' => $router->currentRoute()->getDomain(), /* Result: null  */
    ]);
}, 'home');

$router->get('/{var1}/{var2}', function (Router $router) {
    return new JsonResponse([
        'current_page_name'   => $router->currentRoute()->getName(),   /* Result: home  */
        'current_page_path'    => $router->currentRoute()->getPath(),    /* Result: /     */
        'current_page_method' => $router->currentRoute()->getMethod(), /* Result: GET   */
        'current_page_domain' => $router->currentRoute()->getDomain(), /* Result: null  */
        'current_page_params' => $router->currentRoute()->getParameters(), /* Result: null  */
        'current_page_uri' => $router->currentRoute()->getUri(), /* Result: null  */
    ]);
}, 'page');

$router->dispatch();
