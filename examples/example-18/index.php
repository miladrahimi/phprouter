<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->name('about')->get('/about', function () {
    return 'About.';
});
$router->name('post')->get('/post/{id}', function ($id) {
    return 'Content of the post: ' . $id;
});
$router->name('home')->get('/', function (Router $router) {
    return new JsonResponse([
        'links' => [
            'about' => $router->url('about'),             /* Result: /about  */
            'post1' => $router->url('post', ['id' => 1]), /* Result: /post/1 */
            'post2' => $router->url('post', ['id' => 2])  /* Result: /post/2 */
        ]
    ]);
});

$router->dispatch();
