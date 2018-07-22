<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/13/2018 AD
 * Time: 18:33
 */

use MiladRahimi\Router\Exceptions\RouteNotFoundException;
use MiladRahimi\Router\Middleware;
use MiladRahimi\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

require './../vendor/autoload.php';

$router = new Router();

$router->post('/', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
        'body' => $request->getBody(),
        'parsedBody' => $request->getParsedBody(),
        'headers' => $request->getHeaders(),
        'attributes' => $request->getAttributes(),
    ]);
});

$router->post('/create', function () {
    return new HtmlResponse('<html>Object successfully created.</html>', 201);
});

$router->put('/{id}', function ($id) {
    return new TextResponse('The entity with ' . $id . ' updated.');
});

$router->patch('/', function () {
    return new EmptyResponse();
});

$router->get('/redirect', function () {
    return new RedirectResponse('https://miladrahimi.com');
});

$router->get('/query', function (ServerRequest $request) {
    return new JsonResponse([
        'parameter' => $request->getQueryParams(),
    ]);
});

$router->get('/query', function (ServerRequest $request) {
    return new JsonResponse([
        'parameter' => $request->getQueryParams(),
    ]);
});

class AuthMiddleware implements Middleware {

    /**
     * Handle user request
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        if($request->getHeader('Authorization')) {
            return $next($request);
        }

        return new EmptyResponse(401);
    }
}

$router->get('/auth', function () {
    return new TextResponse('OK');
}, AuthMiddleware::class);

// Route Name
$router->useName('about')->get('/about', function (Router $router) {
    if($router->isRoute('about')) {
        return 'Current route is about';
    } else {
        return 'Current route is ' . $router->currentRouteName();
    }
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->publish(new EmptyResponse(404));
} catch (Throwable $e) {
    echo '<pre>' . print_r($e, true) . '</pre>';
}