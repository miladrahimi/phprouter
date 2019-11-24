<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use Zend\Diactoros\Response\HtmlResponse;

$router = new Router();

$router->get('/', function () {
    return 'Home.';
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->getPublisher()->publish(new HtmlResponse('Not found.', 404));
} catch (Throwable $e) {
    // Log and report...
    $router->getPublisher()->publish(new HtmlResponse('Internal error.', 500));
}
