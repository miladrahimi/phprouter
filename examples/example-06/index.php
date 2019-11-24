<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\HtmlResponse;

$router = new Router();

class Controller
{
    function method()
    {
        return new HtmlResponse('Method as a controller');
    }
}

$router->get('/method', 'Controller@method');

$router->dispatch();