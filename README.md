[![Latest Stable Version](https://poser.pugx.org/miladrahimi/phprouter/v/stable)](https://packagist.org/packages/miladrahimi/phprouter)
[![Total Downloads](https://poser.pugx.org/miladrahimi/phprouter/downloads)](https://packagist.org/packages/miladrahimi/phprouter)
[![Build Status](https://travis-ci.org/miladrahimi/phprouter.svg?branch=master)](https://travis-ci.org/miladrahimi/phprouter)
[![Coverage Status](https://coveralls.io/repos/github/miladrahimi/phprouter/badge.svg?branch=master)](https://coveralls.io/github/miladrahimi/phprouter?branch=master)
[![License](https://poser.pugx.org/miladrahimi/phprouter/license)](https://packagist.org/packages/miladrahimi/phprouter)

# PhpRouter

PhpRouter is a powerful, standalone, and very fast HTTP URL router for PHP projects.

Supported features:
* Multiple controller types (class, closure, and function)
* Route parameters
* Predefined route parameter regex patterns
* Middleware
* Route groups (URI prefix, namespace prefix, middleware, and domain)
* Route names
* Multiple domains or subdomains (regex pattern)
* Custom HTTP methods
* PSR-7 requests and responses
* Request, response and router instance injection

## Versions

* **v4.x.x (LTS)**
* v3.x.x (Unsupported)
* v2.x.x (Unavailable)
* v1.x.x (Unavailable)

## Installation

Install [Composer](https://getcomposer.org) and run following command in your project's root directory:

```bash
composer require miladrahimi/phprouter "4.*"
```

## Configuration

First of all, you need to configure your webserver to handle all the HTTP requests with a single PHP file like `index.php`. Here you can see sample configurations for Apache HTTP Server and NGINX.

* Apache `.htaccess` sample:
    ```
    <IfModule mod_rewrite.c>
        <IfModule mod_negotiation.c>
            Options -MultiViews
        </IfModule>

        RewriteEngine On

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)/$ /$1 [L,R=301]

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </IfModule>
    ```

* NGINX configuration sample:
    ```nginx
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    ```

## Getting Started

After the configurations mentioned above, you can start using PhpRouter in your entry point file (`index.php`) like this example:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/', function () {
    return '<p>This is homepage!</p>';
});

$router->dispatch();
```

There are more examples [here](https://github.com/miladrahimi/phprouter/tree/master/examples).

## HTTP Methods

Here you can see how to declare different routes for different HTTP methods:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router
    ->get('/', function () {
        return '<b>GET method</b>';
    })
    ->post('/', function () {
        return '<b>POST method</b>';
    })
    ->patch('/', function () {
        return '<b>PATCH method</b>';
    })
    ->put('/', function () {
        return '<b>PUT method</b>';
    })
    ->delete('/', function () {
        return '<b>DELETE method</b>';
    })
    ->any('/page', function () {
         return 'This is the Page! No matter what the HTTP method is!';
    })
    ->dispatch();
```

You may want to use your custom HTTP methods, so take a look at this example:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router
    ->map('GET', '/', function () {
        return '<b>GET method</b>';
    })
    ->map('POST', '/', function () {
        return '<b>POST method</b>';
    })
    ->map('CUSTOM', '/', function () {
        return '<b>CUSTOM method</b>';
    })
    ->dispatch();
```

## Controllers

PhpRouter supports plenty of controller types, just look at the following examples.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/closure', function () {
    return 'Closure as a controller';
});

function func() {
    return 'Function as a controller';
}
$router->get('/function', 'func');

$router->dispatch();
```

And class controllers:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

class Controller
{
    function method()
    {
        return 'Class method as a controller';
    }
}

$router->get('/method', 'Controller@method');

$router->dispatch();
```

If your controller class has a namespace:

```php
use App\Controllers\HomeController;
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/ns', 'App\Controllers\TheController@show');
// OR
$router->get('/ns', TheController::class . '@show');

$router->dispatch();
```

If your controllers have the same namespace or namespace prefix, you can pass it to the router constructor like this:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router('', 'App\Controllers');

$router->get('/', 'TheController@show');
// PhpRouter looks for App\Controllers\TheController@show

$router->dispatch();
```

## Route Parameters

A URL might have one or more variable parts like the id in a blog post URL. We call it the route parameter. You can catch them by controller parameters with the same names.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

// Required parameter
$router->get('/post/{id}', function ($id) {
    return "The content of post $id";
});

// Optional parameter
$router->get('/welcome/{name?}', function ($name = null) {
    return 'Welcome ' . ($name ?: 'Dear User');
});

// Optional parameter, Optional Slash!
$router->get('/profile/?{user?}', function ($user = null) {
    return ($user ?: 'Your') . ' profile';
});

// Optional parameter with default value
$router->get('/role/{role?}', function ($role = 'admin') {
    return "Role is $role";
});

// Multiple parameters
$router->get('/post/{pid}/comment/{cid}', function ($pid, $cid) {
    return "The comment $cid of the post $pid";
});

$router->dispatch();
```

In default, route parameters can be any value, but you can define regex patterns for each of them.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->define('id', '[0-9]+');

$router->get('/blog/post/{id}', function (int $id) {
    return 'Content of the post: ' . $id;
});

$router->dispatch();
```

## HTTP Request and Request

PhpRouter uses [zend-diactoros](https://github.com/zendframework/zend-diactoros) package (v2) to provide [PSR-7](https://www.php-fig.org/psr/psr-7) complaint request and response objects to your controllers and middleware.

### Request

You can catch the PSR-7 request object in your controllers like this example:

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->get('/', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
        'body' => $request->getBody(),
        'parsedBody' => $request->getParsedBody(),
        'headers' => $request->getHeaders(),
        'queryStrings' => $request->getQueryParams(),
        'attributes' => $request->getAttributes(),
    ]);
});

$router->post('/posts', function (ServerRequest $request) {
    $post = new PostModel();
    $post->title = $request->getQueryParams()['title'];
    $post->content = $request->getQueryParams()['content'];
    $post->save();

    return new EmptyResponse(204);
});

$router->dispatch();
```

### Response

The example below illustrates the built-in responses.

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

$router = new Router();

$router
    ->get('/html/1', function () {
        return '<html>This is an HTML response</html>';
    })
    ->get('/html/2', function () {
        return new HtmlResponse('<html>This is also an HTML response</html>', 200);
    })
    ->get('/json', function () {
        return new JsonResponse(['error' => 'Unauthorized!'], 401);
    })
    ->get('/text', function () {
        return new TextResponse('This is a plain text...');
    })
    ->get('/empty', function () {
        return new EmptyResponse(); // HTTP Status: 204
    })
    ->get('/redirect', function () {
        return new RedirectResponse('https://miladrahimi.com');
    });

$router->dispatch();
```

## Middleware

PhpRouter supports middleware. You can use it for different purposes such as authentication, authorization, throttles and so forth. Middleware runs before controllers and it can check and manipulate the request and response.

Here you can see the request lifecycle considering some middleware:

```
[Request] ↦ Router ↦ Middleware 1 ↦ ... ↦ Middleware N ↦ Controller
                                                             ↧
          ↤ Router ↤ Middleware 1 ↤ ... ↤ Middleware N ↤ [Response]
```

To declare a middleware, you must implement the `Middleware` interface. Here is the Middleware interface:

```php
interface Middleware
{
    /**
     * Handle request and response
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface|mixed|null
     */
    public function handle(ServerRequestInterface $request, Closure $next);
}
```

As you can see, a middleware must have a `handle()` method that catches the request and a Closure (which is responsible for running the next middleware or the controller). It must return a response, as well. A middleware can break the lifecycle and return the response or it can run the `$next` closure to continue the lifecycle.

See the following example. In this snippet, if there is an `Authorization` header in the request, it passes the request to the next middleware or the controller (if there is no more middleware left) and if the header is absent, it returns an empty response with `401 Authorization Failed ` HTTP status code.

```php
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
```

Middleware can be implemented using closures but it doesn’t make scense to do so!

## Domain and Sub-domain

Your application may serve different services on different domains or subdomains. In this case, you can specify the domain or subdomain for your routes. See this example:

```php
$router = new Router();

// Domain
$router->get('/', 'Controller@method', [], 'domain2.com');

// Sub-domain
$router->get('/', 'Controller@method', [], 'server2.domain.com');

// Sub-domain with regex pattern
$router->get('/', 'Controller@method', [], '(.*).domain.com');

$router->dispatch();
```

## Route Groups

Application routes can be categorized into groups if they have common attributes like middleware, domain, or prefix. The following example shows how to group routes:

```php
use MiladRahimi\PhpRouter\Examples\Samples\SimpleMiddleware;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Enums\GroupAttributes;

$router = new Router();

// A group with uri prefix
$router->group(['prefix' => '/admin'], function (Router $router) {
    // URI: /admin/setting
    $router->get('/setting', function () {
        return 'Setting.';
    });
});

// All of group properties together!
$attributes = [
    'prefix' => '/products',
    'namespace' => 'App\Controllers',
    'domain' => 'shop.example.com',
    'middleware' => SimpleMiddleware::class,
];

// A group with many common properties!
$router->group($attributes, function (Router $router) {
    // URI: http://shop.example.com/products/{id}
    // Controller: App\Controllers\ShopController@getProduct
    // Domain: shop.example.com
    // Middleware: SampleMiddleware
    $router->get('/{id}', function ($id) {
        return 'Wow.';
    });
});

$router->dispatch();
```

## URI Prefix

Your project might be in a subdirectory, or all of your routes might start with the same prefix. You can pass this prefix as the constructor like this example:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router('/shop');

// URI: /shop/about
$router->get('/about', function () {
    return 'About the shop.';
});

// URI: /shop/product/{id}
$router->get('/product/{id}', function ($id) {
    return 'A product.';
});

$router->dispatch();
```

## Route Name

You can define names for your routes and use them in your codes instead of the URLs. See this example:

```php
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
```

## Current Route

You might want to get information about the current route in your controller. This example shows how to get this information.

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->name('home')->get('/', function (Router $router) {
    return new JsonResponse([
        'current_page_name'   => $router->currentRoute()->getName(),   /* Result: home  */
        'current_page_uri'    => $router->currentRoute()->getUri(),    /* Result: /     */
        'current_page_method' => $router->currentRoute()->getMethod(), /* Result: GET   */
        'current_page_domain' => $router->currentRoute()->getDomain(), /* Result: null  */
    ]);
});

$router->dispatch();
```

## Error Handling

Your application runs through the `Router::dispatch()` method. You should put it in a `try` block and catch exceptions that will be thrown by your application and PhpRouter.

```php
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
```

PhpRouter also throws the following exceptions:

* `RouteNotFoundException` if PhpRouter cannot find any route that matches the user request.
* `InvalidControllerException` if PhpRouter cannot invoke the controller.
* `InvalidMiddlewareException` if PhpRouter cannot invoke the middleware.
* `UndefinedRouteException` if `Router::url()` cannot find any route with the given name.

The `RouteNotFoundException` should be considered `404 Not found` error.

The `InvalidControllerException` and `InvalidMiddlewareException` exceptions should never be thrown normally, so they should be considered `500 Internal Error`.

## License

PhpRouter is initially created by [Milad Rahimi](https://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).

