<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Examples\Shared\SimpleController;
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\Url;

$router = Router::create();

$router->get('/', [SimpleController::class, 'show'], 'home');
$router->get('/post/{id}', [SimpleController::class, 'show'], 'post');
$router->get('/links', function (Url $url) {
    return new JsonResponse([
        'about' => $url->make('home'),              /* Result: /about  */
        'post1' => $url->make('post', ['id' => 1]), /* Result: /post/1 */
        'post2' => $url->make('post', ['id' => 2])  /* Result: /post/2 */
    ]);
});

$router->dispatch();
