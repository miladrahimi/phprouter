# PHPRouter
Free PHP URL router for neat and powerful projects!

## Documentation
PHPRouter is a free, neat, powerful and stand-alone URL router for PHP projects.
It's inspired by [Laravel](http://laravel.com/docs/master/routing) routing system and
appropriate for no-framework projects and brand new frameworks.

### URL Routing
URL Routing means mapping URLs to controllers.
Consider following URL:
```
http://example.com/index.php?section=blog&page_type=post&id=93
```
The URL above is probably mapped to a controller which runs a code snippet like:
```
$post = new Blog\Post();
$post->show(93);
```
Modern URL routers (like PHPRouter) provide full control on URL,
So you can map a shorter URL like:
```
http://example.com/blog/post/93
```

### Installation
#### Using Composer
It's strongly recommended to use [Composer](http://getcomposer.org) to add PHPRouter to your application.
If you are not familiar with Composer, The article
[How to use composer in php projects](http://www.miladrahimi.com/blog/2015/04/12/how-to-use-composer-in-php-projects)
can be useful.
After installing Composer, go to your project directory and run following command there:
```
php composer.phar require miladrahimi/phprouter
```
Or if you have `composer.json` file already in your application,
you may add this package to your application requirements
and update your dependencies:
```
"require": {
    "miladrahimi/phprouter": "dev-master"
}
```
```
php composer.phar update
```
#### Manually
You can use your own autoloader as long as it follows [PSR-0](http://www.php-fig.org/psr/psr-0) or
[PSR-4](http://www.php-fig.org/psr/psr-4) standards.
In this case you can put `src` directory content in your vendor directory.

### Getting Started
All of your application requests must be handled by one PHP file like `index.php`.
Edit the `.htaccess` file achieve this goal:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.*$ index.php [PT,L]
```
Now you can use `Router` class in the PHP file (here `index.php`) to route your application:
```
// Use this namespace
use MiladRahimi\PHPRouter\Router;

// Create brand new Router object
$router = new Router();

// Map this function to home page
$router->get("/", function () {
    return "This is home page!";
});

// Dispatch all matched routes and run!
$router->dispatch();
```

### Basic Routing
To buy more convenience, most of controllers in the examples are defined as closure.
Of course, PHPRouter supports plenty of controller types which you will read in further sections.
There are some simple routes below.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Map this function to home page (GET method)
$router->get("/", function () {
    return "This is home page!";
});

// Map this function to /blog URI (GET method)
$router->get("/blog", function () {
    return "This is blog!";
});

// Map this function to /submit URI (POST method)
$router->post("/submit", function () {
    return "I'm supposed to catch posted data!";
});

$router->dispatch();
```
*   All of controllers above are closure functions.
*   The `get()` method map controllers to `GET` request methods.
*   The `post()` method map controllers to `POST` request methods.

### Request methods
Both `get()` and `post()` methods in `Router` class are shortcuts for mapping controllers to `GET` and `POST` requests.
The super method is `map()` which catches request method as its first argument.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Alias: $router->get();
$router->map("GET", "/", function () {
    return "This is Home!";
});

// Alias: $router->post();
$router->map("POST", "/post_data", function () {
    return "Data updated!";
});

// PUT Request method
$router->map("PUT", "/put_data", function () {
    return "Data uploaded!";
});

// DELETE Request method
$router->map("DELETE", "/delete_data", function () {
    return "Data deleted!";
});

$router->dispatch();
```

### Multiple request methods
The controller can be mapped to multiple request methods as following example demonstrates:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Map to GET, POST and DELETE request methods
$router->map(["GET", "POST", "DELETE"], "/", function () {
    return "Homepage for GET, POST and DELETE request methods!";
});

$router->dispatch();
```
*   PHP >= 5.4 supports `[]` [syntax for array](http://php.net/manual/en/language.types.array.php).
    you may use old `array()` syntax.

### Any request method
If the controller can respond to the route with any request method, you may try this method:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Respond to all request methods (GET, POST, PUT, etc)
$router->any("/", function () {
    return "This is Home! No matter what the request method is!";
});

$router->dispatch();
```

### Multiple routes
Array of routes is supported too. If the controller can respond to multiple routes,
the method below may be useful to you.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->map(["GET", "POST"], ["/", "/home"], function () {
    return "Homepage for GET, POST and DELETE requests!";
});

$router->dispatch();
```
*   The controller above will respond to these request methods and routes:
    *   Method: `GET`   Route: `/`
    *   Method: `GET`   Route: `/home`
    *   Method: `POST`  Route: `/`
    *   Method: `POST`  Route: `/home`

### Controllers
Personally, I hate using closure as a controller.
I believe a controller absolutely must be a method.
However, following codes shows how to use all kind of controllers.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Closure
$router->get("/1", function () {
    return "Closure as controller";
});

// Stored closure
$closure = function() {
    return "Stored closure as controller";
};
$router->get("/2", $closure);

// Function
function func() {
    return "Function as controller";
}
$router->get("/3", "func");

// Method (Recommended)
class Controller
{
    function method()
    {
        return "Method as controller";
    }
}
$router->get("/4", "Controller@method");

$router->dispatch();
```

### Controller class namespaces
Because of MVC pattern, developers usually declare controllers with namespaces.
Current version of PHPRouter doesn't recognize "used namespaces",
so you have to pass them with the class names.
See following example which `Post` class is declared with `Blog` namespace.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/posts", 'Blog\Post@all');

$router->dispatch();
```
The `Post` class:
```
<?php namespace Blog;

class Post {
    function all() {
        return "All posts";
    }
}
```

### Route parameters
Router is build to help developers to handle dynamic routes easier than ever.
Let's go back to the first example, I mean this one:
```
http://example.com/blog/post/93
```
The post ID (`93` in the example above) is variable.
Actually we need to handle all the routes with following pattern with only one controller:
```
http://example.com/blog/post/{id}
```
Don't worry while you use PHPRouter!
Because you can handle it in the easiest way.
Look at following example:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/post/{id}", function ($id) {
    return "Show content of post " . $id;
});

$router->dispatch();
```

Or you may use a method as the controller:
```
class Blog
{
    function showPost($id)
    {
        return "Show content of post " . $id;
    }
}

use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/post/{id}", "Blog@showPost");

$router->dispatch();
```
*   You can determine route parameters with `{` and `}` characters.
*   The Router will extract them and pass their values as arguments to the controller.
*   Controller parameters must has the same route parameter names.

### Optional Parameters
You may consider one or some of parameters optional.
In this example `id` is optional.
If request URI had ID, it would return page with the caught ID.
If request URI was without ID (`/page/`), it would return `All pages!`.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/page/{id?}", function ($id) {
    if($id == null)
        return "All pages!";
    return "Page $id";
});

$router->dispatch();
```
*   Parameters which hasn't any value will be `null`.

In the example above to see all pages, user must enter `/page/` URI.
You may consider `/page` for this purpose too.
To achieve that you can use array of routes or use following trick:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/page/?{id?}", function ($id) {
    if($id == null)
        return "All pages!";
    return "Page $id";
});

$router->dispatch();
```
*   `?` makes preceding character optional.

### More customized parameters
In default, route parameters can be everything (`[^/]+` Regular Expression pattern).
Sometimes the route parameter must be only a numeric value.
Sometimes you need them to follow a complex pattern.
No problem! You can define
[Regular Expression](http://www.regular-expressions.info/)
pattern for some or all of the route parameters.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// 'id' must be a number
$router->define("id","[0-9]+");

// 'username' must be an alphanumeric, - is allowed too
$router->define("username","[a-z0-9\-]+");

$router->get("/blog/post/{id}", function ($id) {
    return "Show content of post " . $id;
});

$router->get("/user/{username}", function ($username) {
    return "Show info of user with username: " . $username;
});

$router->dispatch();
```
*   No need to check if ID is a number of not, I will be a number, I promise!
*   To avoid unwanted results, pattern group characters (`(` and `)`) are disabled.

### Request and Response objects
The Router passes two objects named `$request` and `$response` to the route controller.
Of course the controller must be considered to get them too.
These objects help you in some cases.
To get these object you must name them (one or both) in the controller parameters.
Don't worry, PHPRouter is enough flexible to don't care about the order of controller parameters!
#### Request object
The URL:
```
http://example.com/blog/post/93?section=comments
```
The application routing section:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/post/{id}", function ($id, $request) {
    echo $id . "<br>";
    echo $request->getUrl() . "<br>";
    echo $request->getUri() . "<br>";
    echo $request->getWebsite() . "<br>";
    echo $request->getPage() . "<br>";
    echo $request->getQueryString() . "<br>";
    echo $request->getBaseUri() . "<br>";
    echo $request->getLocalUri() . "<br>";
    echo $request->getMethod() . "<br>";
    echo $request->getProtocol() . "<br>";
    echo $request->getIP() . "<br>";
    echo $request->getPort() . "<br>";
});

$router->dispatch();
```
The output:
```
93
example.com/blog/post/93?section=comments
/blog/post/93?section=comments
example.com
/blog/post/93
section=comments

/blog/post/93
GET
HTTP/1.1
127.0.0.1
14889
```

Other methods:
```
// Return Previous URL (HTTP_REFERRER)
$request->referer();
// Following method will be discussed more!
$request->get();
$request->get("key");
$request->post();
$request->post("key");
$request->cookie();
$request->cookie("name");
```

#### Response object

Response object manipulates the application HTTP response to the client. Here is an example:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/fa", function ($response) {
    $response->render("test.php");
    $response->publish("Here is the published content!");
});

$router->dispatch();
```
The output:
```
This is test.php file content!
Here is the published content!
```
Methods:
```
$response->publish($content);       // Publish string, array or object content
$response->cookie($name,$value);    // Set cookie value (using PHP native setcookie() function)
$response->redirect($to);           // Redirect to the new URL ($to)
$response->render($file);           // Render PHP file (using PHP include syntax)
$response->contents();              // Return current output 
```

If you use IDEs like PhpStorm, it'd be good practice not to omit `$request` and `$response` data types.
Then your IDE can help you and provide auto-complete options.
See example below:
```
use MiladRahimi\PHPRouter\Router;
use MiladRahimi\PHPRouter\Request;
use MiladRahimi\PHPRouter\Response;

$router = new Router();

$router->get("/page/{num}", function ($num, Request $request, Response $response) {
    return "Page $num <br> User IP:" . $request->ip();
});

$router->dispatch();
```

### Access $_GET, $_POST and $_COOKIE
Of course you can use the same `$_GET`, `$_POST` and `$_COOKIE` arrays.
But it might neater and more beautiful to use the `$request` methods.
Run following example for `http://example.com/user?id=93`:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/user", function ($request) {
    return $request->get("id"); // will publish "93"
});

$router->dispatch();
```
#### GET
The `get()` method in `$request` object is used to access GET parameters.
Following example returns $_GET array when the `$key` parameter is absent.
```
$request->get();
```
But this time, it would return value of `id` parameter if it had existed:
```
$request->get("id");
```
#### POST
The `post()` method in `$request` object is used to access POST parameters.
Following example returns $_POST array when the `$key` parameter is absent.
```
$request->post();
```
But this time, it would return value of `id` parameter if it had existed:
```
$request->post("id");
```
#### COOKIE
The `cookie()` method in `$request` object is used to access cookies.
Following example returns all cookies (as an array) when the `$name` parameter is absent.
```
$request->cookie();
```
But this time, it would return value of `id` in cookies if it had existed:
```
$request->cookie("id");
```
The `cookie()` method in `$response` object is used to manipulate or write cookies.
Following example shows the way it works:
```
$response->cookie("language","persian");
```
*   `$response->cookie()` arguments: `$name`, `$value`, `$expire`, `$path`, `$domain`, `$secure`, `$httponly`

### Redirection
You can redirect the request with the php `header()` function.
But if you are interested in using PHPRouter methods, you may try this:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/old-page", function ($response) {
    $response->redirect("/new-page");
});

$router->dispatch();
```
*   The `redirect()` method will consider the **Base URI**.
*   If you omit `$to` parameters, it will be `"/"` in default and it will redirect client to home.

### Rendering PHP files
Sometimes the result you want to return is not plain texts or compiled PHP codes but it's a PHP file.
No problem! you can use `render()` method in `$response` object as it's shown in the following example:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/", function ($response) {
    $response->render("test.php");
});

$router->dispatch();
```
*   It's recommended to use a **template engine** which returns HTML content instead.

### Middleware
Middleware is a function or any callable which runs before your controller.
It can control access like authentication or anything you want.
You can consider one or more middleware for the route.
Actually the `$middleware` parameter can be a callable, a method name or an array of them.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

// Controller
function profile($id)
{
    return "The info of user with this ID:" . $id;
}

$router->get("/user/{id}", "profile", "authenticate");

$router->dispatch();
```
*   All the parameters which Router passes to the controller will be passed to the middleware too.
*   In this level middleware seems not very useful.
    Read the rest of documentation, you will find it very useful!

### Groups
It's the most exciting part! You can group your routes and set common options for them.
In this version PHPRouter supports common
**middleware**, **domain**, **subdomain**, **prefix** and **postfix**.

For example all of user control panel pages need authentication.
So you can group all of them.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

$router->group("authenticate", function ($router) {

    $router->get("/user/{id}/profile", function ($id) {
        return "User profile page!";
    });

    $router->get("/user/{id}/setting", function ($id) {
        return "User setting page!";
    });

    $router->get("/user/{id}/messages", function ($id) {
        return "User messages page!";
    });

});

$router->dispatch();
```
*   First argument of `group()` method is common options. You can pass all commons in an array.
*   If you pass a callable function or a name of function or method, it will be considered as middleware.
*   Second argument of `group()` method is a function or method which declares group body (routes).
*   The body must access the `$router` object somehow, It will if you consider `$router` parameter.
*   You may use PHP `use` statement instead of putting `$router` parameter for group body.

You may use **prefix** option beside of middleware.
```
use MiladRahimi\PHPRouter\Router;
$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

$options = ["middleware" => "authenticate", "prefix" => "/user"]; // This line!

$router->group($options, function (Router $router) { // And this line too!

    $router->get("/{id}/profile", function ($id) {
        return "User $id profile page!";
    });

    $router->get("/{id}/setting", function ($id) {
        return "User $id setting page!";
    });

    $router->get("/{id}/messages", function ($id) {
        return "User $id messages page!";
    });

});

$router->dispatch();
```
*   The `middleware` element can be callable function, name of a function or method or array of them.
*   The `prefix` and `postfix` elements are string, array of them is not permitted.
*   You may put `$router` data type (`Router`) in the group body parameters to help your IDE.
*   If you use this way of injecting Router object to the group body,
    the object's name in the body will be `$router` always.

You may use PHP `use` syntax to access the router object.
It's useful specially when the router object name is not `$router`!
```
use MiladRahimi\PHPRouter\Router;

$my_router = new Router();

// Middleware
function authenticate($id, $response)
{
    if (is_permitted_user($id) == false) {
        $response->redirect("/forbidden");
    }
}

$options = ["middleware" => "authenticate", "prefix" => "/user"];

$my_router->group($options, function () use ($my_router) { // This line!

    $my_router->get("/{id}/profile", function ($id) {
        return "User $id profile page!";
    });

    // ...

});

$router->dispatch();
```
*   When you use `use` syntax, you can keep your Router object's name in the group body.

### Domains
You can have some different websites on one hosting!
The `domain` element in the first argument of `group()` method will help you.
Check out following example:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->group(["domain" => "domain1.com"], function () use ($router) {
    $router->get("/", function () {
        return "Homepage of domain1.com";
    });
});

$router->group(["domain" => "domain1.com"], function () use ($router) {
    $router->get("/", function () {
        return "Homepage of domain2.com";
    });
});

$router->dispatch();
```
*   You can use other common options like middleware beside of domain option.

### Subdomains
It's really easy to manage subdomains with PHPRouter.
The `domain` element in the first argument of `group()` method will help you.
As mentioned above this element is used to manage domains too,
but this time we work with subdomains.
#### Static subdomains
Following Example shows how to work with `blog` and `forum` subdomains:
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->group(["domain" => "blog.example.com"], function () use ($router) {
    $router->get("/", function () {
        return "Blog home page!";
    });
});

$router->group(["domain" => "forum.example.com"], function () use ($router) {
    $router->get("/", function () {
        return "Forum home page!";
    });
});

$router->dispatch();
```
#### Dynamic subdomains
You can catch subdomain as a parameter just like route parameter.
See the following example.
it's really nice feature.
```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->group(["domain" => "{subdomain}.example.com"], function () use ($router) {
    $router->get("/", function ($subdomain) {
        return "This is " . $subdomain;
    });
});

$router->dispatch();
```
*   You can make parameter in domain wherever you need and catch them in controller parameters.
*   Route parameters would overwrite subdomains if their name was the same.
*   You can use other common options like middleware next to domain option.
*   Just like route parameters, middleware will can access the subdomains.

### Base URI and projects in subdirectory
You can handle routes for the projects in subdirectories with the tools explained above.
But there is a tool to make this job even easier.
For example you work on a blog and all of its files are in directory like `blog`.
You can set it's base URI to route you blog easier.
It's useful in some cases like redirection too, the `redirect()` in `$response` object adds base URI to the target.
```
use MiladRahimi\PHPRouter\Router;

// You can determine the base URI with Router constructor
$router = new Router("/blog");

$router->get("/post/{id}", function ($id) {
    return "Show post " . $id;
});

$router->dispatch();
```
*   You can set base URI only in constructor to keep it unique in whole the application.
*   There is a method named `getBaseURI()` but there is no `setBaseURI` method!

### Errors
To be neater, all of the errors (exceptions) will be thrown when you call `dispatch()` method.
So you must wrap this method with `try-catch`.
```
use MiladRahimi\PHPRouter\Router;
use MiladRahimi\PHPRouter\HttpError;
use MiladRahimi\PHPRouter\PHPRouterError;

$router = new Router();

$router->get("/", function() {
    return "Homepage!";
});

try {
    $router->dispatch();
} catch(HttpError $e) {
    if($e->getMessage() == "404")
        $router->publish("Error 404! Not found!");
    //...

} catch(PHPRouterError $e) {
    // Log details...
    $router->publish("Sorry, there is an internal error, we will fix it asap!");
}
```
*   You may use `publish()` method or PHP `echo()` or anything you like to publish details for users.

#### HttpError Exception
This exception will be thrown when an HTTP error like **Error 404 Not Found** occurs.
The exception message contain it's code like `404`.

#### PHPRouterError Exception
This exception is the main package exception
and will be thrown when an Internal error (like passing undefined function as controller) occurs.
You can see all this exception error codes in the [official page](http://neatplex.com/package/phprouter/errors).

#### Your application exceptions
PHPRouter doesn't manipulate your application exceptions,
so you can catch them like `HttpError` and `PHPRouterError` exceptions.

### Template Engine
When you use a router for your application, you will find it out soon which you need a **template engine** too.
Template engines usually returns HTML output, so you can return it in your controller.
Next MiladRahimi package is a template engine, if you cannot wait awhile,
so [Mustache template engine](https://github.com/bobthecow/mustache.php) is a good option.

## Contributors
*	[Milad Rahimi](http://miladrahimi.com)

## Official homepage
*   [PHPRouter](http://miladrahimi.github.io/phprouter) (Soon!)

## License
PHPRouter is created by [MiladRahimi](http://miladrahimi.com)
and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
