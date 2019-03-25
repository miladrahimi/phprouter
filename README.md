[![Build Status](https://travis-ci.org/miladrahimi/phprouter.svg?branch=master)](https://travis-ci.org/miladrahimi/phprouter)

[![Coverage Status](https://coveralls.io/repos/github/miladrahimi/phprouter/badge.svg?branch=master)](https://coveralls.io/github/miladrahimi/phprouter?branch=master)

# PhpRouter
PhpRouter is a powerful and standalone URL router for PHP projects.

## Installation

Install [Composer](https://getcomposer.org) and run following command in your project's root directory:

```bash
composer require miladrahimi/phprouter "4.*"
```

## Configuration
First of all, you need to configure your web server to handle all the HTTP requests with a single PHP file like `index.php`. Here you can see required configurations for Apache HTTP Server and NGINX.

### Apache
If you are using Apache HTTP server, you must have a file named `.htaccess` in your project's root directory contains following content.

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

### NGINX
If you are using NGINX web server, you should consider following directive in your site configuration file.

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Getting Started
After configurations above, you can start using PhpRouter in your entry point (`index.php`) like this example:

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->get('/', function () {
    return '<p>This is homepage!</p>';
});

$router->post('/blog/post/{id}', function ($id) {
    return HtmlResponse("<p>This is a post $id</p>");
});

$router->patch('/json', function () {
    return JsonResponse(['message' => 'This is a JSON response!']);
});

$router->dispatch();
```

There are also some examples [here](https://github.com/miladrahimi/phprouter/blob/master/examples/index.php).

## HTTP Methods

Here you can see how to declare different routes with different http methods:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router
    ->get('/', function () {
        return '<b>GET method</b>';
    });
    ->post('/', function () {
        return '<b>POST method</b>';
    });
    ->patch('/', function () {
        return '<b>PATCH method</b>';
    });
    ->put('/', function () {
        return '<b>PUT method</b>';
    });
    ->delete('/', function () {
        return '<b>DELETE method</b>';
    })
    ->dispatch();
```

You may want to use your custom http methods so take look at this example:

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

You also may want to respond to all the http methods so this one is for you:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->any('/', function () {
    return 'This is Home! No matter what the HTTP method is!';
});

$router->dispatch();
```

## Controllers

PhpRouter supports plenty of controller types, just look at following examples.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/1', function () {
    return 'Closure as a controller';
});

$closure = function() {
    return 'Stored closure as a controller';
};
$router->get('/2', $closure);

function func() {
    return 'Function as a controller';
}
$router->get('/3', 'func');

$router->dispatch();
```

Using PHP classes for controllers could be a nice idea.

```php
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

$router->get('/4', 'Controller@method');

$router->dispatch();
```

And if your controller class has a namespace:

```php
use App\Controllers\TheController;
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/5', 'App\Controllers\TheController@method');
// OR
$router->get('/5', TheController::class . '@method');

$router->dispatch();
```

Or you can pass the namespace to the Router instance and only write the controller name in the routes this way:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router('', 'App\Controllers');

$router->get('/5', 'TheController@method');
// PhpRouter looks for App\Controllers\TheController@method

$router->dispatch();
```

## Route Parameters

Some endpoints might have variable parts like post id in a post URL. We call them route parameters, and you can catch them by controller parameters with the same names.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

// Required parameter
$router->get('/blog/post/{id}', function ($id) {
    return 'Content of the post: ' . $id;
});

// Optional parameter
$router->get('/path/to/{info?}', function ($info = null) {
    return 'Info may be present or may be NULL.';
});

// Optional parameter, Optional Slash!
$router->get('/path/to/?{info?}', function ($info = null) {
    return 'info may be present or may be NULL.';
});

// Optional parameter with default value
$router->get('/path/to/{info?}', function ($info = 'Default') {
    return 'info may be present or may be Default.';
});

$router->dispatch();
```

In default, route parameters can match any value, but you can define a regular expression for them and it applys to all of them in all the routes.

```php
use MiladRahimi\PhpRouter\Router;

class BlogController
{
    function getPost(int $id)
    {
        return 'Content of the post: ' . $id;
    }
}

$router = new Router();

// ID must be a numeric value
$router->define('id', '[0-9]+');

$router->get('/blog/post/{id}', 'BlogController@getPost');

$router->dispatch();
```

## HTTP Request and Request

PhpRouter uses [zend-diactoros](https://github.com/zendframework/zend-diactoros) package (version 2) to provide [PSR-7](https://www.php-fig.org/psr/psr-7) complaint request and response objects to your controllers and middleware.

###Request

You can catch the request object like this example:

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
        'queryParameters' => $request->getQueryParams(),
        'attributes' => $request->getAttributes(),
    ]);
});

$router->post('/blog/posts', function (ServerRequest $request) {
    $post = new \App\Models\Post();
    $post->title = $request->getQueryParams()['title'];
    $post->content = $request->getQueryParams()['content'];
    $post->save();

    return new EmptyResponse(201);
});

$router->dispatch();
```

### Response

The example below illustrates supported kinds of responses.

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
        return new JsonResponse(['message' => 'Unauthorized!'], 401);
    })
    ->get('/text', function () {
        return new TextResponse('This is a plain text...');
    })
    ->get('/empty', function () {
        return new EmptyResponse();
    });

$router->dispatch();

```

#### Redirection Response

In case of needing to redirecting user to another URL:

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\RedirectResponse;

$router = new Router();

$router
    ->get('/redirect', function () {
        return new RedirectResponse('https://miladrahimi.com');
    })
    ->dispatch();
```

### More about HTTP Request and Response

Since PhpRouter uses [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) for http request and responses, you should read its documentation to see all of its functionality.

## Middleware

PhpRouter supports middleware, you can use it for different purposes like authentication, authorization, throttles and so forth. Middleware run before controllers and it can check and manipulate http requests.

Here you can see the request lifecycle considering some middleware:

```
 Input --[Request]↦ Router ↦ Middleware 1 ↦ ... ↦ Middleware N ↦ Controller
                                                                      ↧
Output ↤[Response]- Router ↤ Middleware 1 ↤ ... ↤ Middleware N ↤ [Response]
```

To declare a middleware, you must implements Middleware interface. See the interface:

```php
interface Middleware
{
    /**
     * Handle request and response
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface|mixed
     */
    public function handle(ServerRequestInterface $request, Closure $next);
}
```

As you can see, middleware must have a `handle()` method that catches http request and a closure (which runs the next middleware or the controller) and it returns a response at the end. Middleware can break the lifecycle and return a response itself or it can run the `$next` closure to continue lifecycle.

For example see the following snippet. In this snippet, if there was a `Authorization` header in the request,
it passes the request to the next middleware or the controller (if there is no more middleware left) and if the header is absent it returns an empty response with `401 Authorization Failed ` HTTP status code.

```php
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware implements Middleware
{
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        if ($request->getHeader('Authorization')) {
            return $next($request);
        }

        return new EmptyResponse(401);
    }
}

$router = new Router();

$router->get('/auth', function () { return 'OK' }, AuthMiddleware::class);

$router->dispatch();
```

Middleware can be implemented using closures but it doesn’t make scense to do so!

## Domain and Subdomain

Your application may serve different services on different domains/subdomains or it may assign subdomain dynamically to users or services. In this case, you need to specify domain or subdomain in addition to the URIs in your routes.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

// Domain
$router->get('/', 'Controller@method', [], 'domain2.com');

// Subdomain
$router->get('/', 'Controller@method', [], 'server2.domain.com');

// Subdomain regex pattern
$router->get('/', 'Controller@method', [], '(.*).domain.com');

$router->dispatch();
```

Notice that domain parameter receives a regex pattern not a simple string.

## Route Groups

Usually routes can fit in a groups that have common attributes like middleware, domain/subdomain and prefix. To group routes you can follow the example below.

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->group(['prefix' => '/admin'], function (Router $router) {
      // URI: /admin/setting
    $router->get('/setting', 'AdminController@getSetting');
});

$attributes = [
    'prefix'        => '/products',
    'namespace'     => 'App\Controllers',
    'domain'        => 'shop.example.com',
    'middleware'    => SampleMiddleware::class,
];

$router->group($attributes, function (Router $router) {
    // URI: http://shop.example.com/products/{id}
    // Controller: App\Controllers\ShopController@getProduct
    // Domain: shop.example.com
    // Middleware: SampleMiddleware
    $router->get('/{id}', 'ShopController@getProduct');
});

$router->dispatch();
```

As you can see in the examples, you can use `GroupAttributes` enum instead of memorizing attribute names!

## Base URI

Your project may be in a subdirectory, so all of your route URIs will starts with the subdirectory name. You can pass this subdirectory name as the initialize prefix to the PhpRouter this way:

```php
use MiladRahimi\PhpRouter\Router;

$router = new Router('/shop');

// URI: /shop/about
$router->get('/about', 'ShopController@getAbout');

// URI: /shop/product/{id}
$router->get('/product/{id}', 'ShopController@getProduct');

$router->dispatch();
```

## Route Name
You can name your routes and use the names in your controllers and views instead of the URLs so you can change URI patterns without breaking links. See this example:

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->name('about')->get('/about', function () {
    return 'About me!'
});
$router->name('help')->get('/help', function () {
    return 'Help me!'
});
$router->name('page')->get('/page/{id}', function ($id) {
    return 'Content of the page: ' . $id;
});
$router->name('home')->get('/', function (Router $router) {
    return new JsonResponse([
        "link_about" => $router->url('about'), /* /about */
        "link_help" => $router->url('help') /* /help */
        "link_page_1" => $router->url('page', ['id' => 1]), /* /page/1 */
        "link_page_2" => $router->url('page', ['id' => 2]) /* /page/2 */
    ]);
});

$router->dispatch();
```

## Current Route

You might want to get information about current route in your controller. This example shows how to get this information

```php
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->name('home')->get('/', function (Router $router) {
    return JsonResponse([
        "current_page_name" => $router->currentRoute()->getName() /* home */
        "current_page_uri" => $router->currentRoute()->getUri() /* /home */
        "current_page_method" => $router->currentRoute()->getMethod() /* GET */
        "current_page_domain" => $router->currentRoute()->getDomain() /* NULL */
    ]);
});

$router->dispatch();
```

## Error Handling

Your application runs through the `Router::disptach()` method, you should put it in a `try` block and catch exceptions that will be thrown by your application and the router.

```php
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;

$router = new Router();

$router->get('/', function () {
    return 'This is home page!';
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->getPublisher()->publish(new EmptyResponse(404));
} catch (Throwable $e) {
    // other exceptions...
}
```

The router also throws following exceptions:

* `RouteNotFoundException` if cannot find any route for the user request.
* `InvalidControllerException` if the controller is neither callable nor class method.
* `InvalidMiddlewareException` if the middleware is neither callable nor an instance of `Middleware`.

The `RouteNotFoundException` should be considered `404 Not found` error.

The `InvalidControllerException` and `InvalidMiddlewareException` exceptions should never be thrown, they should be considered `500 Internal Error` if these exceptions be thrown.

## License

PhpRouter is initially created by [Milad Rahimi](http://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).

