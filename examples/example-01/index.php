<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->get('/', function () {
    return '<p>This is homepage!</p>';
});

$router->post('/api/user/{id}', function ($id) {
    return new JsonResponse(["message" => "posted data to user: $id"]);
});

$router->dispatch();
