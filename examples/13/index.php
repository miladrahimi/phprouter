<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class AuthMiddleware
{
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        if ($request->getHeader('Authorization')) {
            // Check the auth header...
            return $next($request);
        }

        return new JsonResponse(['error' => 'Unauthorized!'], 401);
    }
}

$router = Router::create();

$router->group(['middleware' => [AuthMiddleware::class]], function(Router $router) {
    $router->get('/admin', function () {
        return 'Admin Panel';
    });
});

$router->dispatch();
