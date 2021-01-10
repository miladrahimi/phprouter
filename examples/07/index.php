<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

class UsersController
{
    function index()
    {
        return 'Class: UsersController, Method: index';
    }

    function handle()
    {
        return 'Class UsersController.';
    }
}

$router = Router::create();

// Controller: Class=UsersController Method=index()
$router->get('/method', [UsersController::class, 'index']);

// Controller: Class=UsersController Method=handle()
$router->get('/class', UsersController::class);

$router->dispatch();
