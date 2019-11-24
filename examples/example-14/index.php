<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class AuthMiddleware implements Middleware
{
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        if ($request->getHeader('Authorization')) {
            return $next($request);
        }

        return new JsonResponse(['error' => 'Unauthorized!'], 401);
    }
}

$router = new Router();

$router->get('/admin', function () {
    return 'This is admin panel!';
}, AuthMiddleware::class);

$router->dispatch();