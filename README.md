# PHPRouter
Free PHP router for neat and powerful projects!

## Documentation
Neatplex PHPRouter is a free, neat, powerful and stand-alone router for PHP projects.
It's inspired by Laravel framework router and appropriate for no-framework projects and brand new frameworks.

### Routing
Take a look at website URL pattern without router:
```
http://example.com/blog.php?page_type=post&id=93
```
And a website equipped with a router:
```
http://example.com/blog/post/93
```
But what happened and how?
In the second website all user requests will be handled by one PHP file like `index.php`.
The PHP file uses a router.
The router checks the request URI and method then call appropriate function or method to respond the request.
For example when the URI is `/blog/post/93`, the router will call a method like `$blog->showPost(93);`,
so the browser will display what this method returns.

### Installation
It's strongly recommended to use [Composer](http://getcomposer.org) to install Neatplex PHPRouter.
If you are not familiar with Composer, just read
[How to use composer in php projects](http://www.miladrahimi.com/blog/2015/04/12/how-to-use-composer-in-php-projects)
article.
After installing Composer, go to your project directory and run following command there:
```
php composer.phar require neatplex/phprouter
```
Or if you have `composer.json` file already in your application,
you may add this package to your application requirements
and update your dependencies:
```
"require": {
    "neatplex/phprouter": "dev-master"
}
```
```
php composer.phar update
```
If you don't want to use Composer (I really don't know why!) you can download the package and put its folder into
your application folders.
Of course in this case, your project has to follow [PSR-0](http://www.php-fig.org/psr/psr-0) or
[PSR-4](http://www.php-fig.org/psr/psr-4) standards for autoloading application classes and packages.
Or you may simply include all the package files
and prepend namespace using statements in the file you call the package
methods (strongly not recommended).

### Getting Started
All of your website requests must be handled by one PHP file like `index.php`.
Edit the `.htaccess` file achieve this goal:
```
## .htaccess file contents
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.*$ index.php [PT,L]
```
Now you can use `Router` class in the PHP file (here `index.php`) to route your application:
```
// use this namespace
use Neatplex\PHPRouter\Router;

// brand new Router object
$router = new Router();

// match this function for home (GET method)
$router->get("/", function () {
    return "This is home!";
});

// match this function for /blog URI (GET method)
$router->get("/blog", function () {
    return "This is blog!";
});

// dispatch all matched routes and run!
$router->dispatch();
```
*   The example above matches controller for home (`/`) and blog (`/blog`) pages.
*   Both of controllers are closure functions.
*   The `get()` method matches controllers for `GET` request methods.

### POST and other request methods
Both `get()` and `post()` methods in `Router` class are shortcuts to matching controller for `GET` and `POST` requests.
But the main method is `match()` which you can use to match for all kind of request methods, as following example shows:
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->match("GET", "/", function () {
    return "This is Home!";
});

$router->match("POST", "/post_data", function () {
    return "Data updated!";
});

$router->match("PUT", "/put_data", function () {
    return "Data uploaded!";
});

$router->match("DELETE", "/delete_data", function () {
    return "Data deleted!";
});

$router->dispatch();
```

### Array of request methods
Because of flexibility, All of `match()`, `get()`, `post()` and `any()` arguments
are able to be an array except controller.
For now we try array of request methods for `match()` method.
For example following route will respond to `/` URI when the request is `GET`, `POST` or `DELETE`.
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->match(array("GET", "POST", "DELETE"), "/", function () {
    return "Homepage for GET, POST and DELETE request methods!";
});

$router->dispatch();
```

### Any request method
Sometimes a controller can handle the URI regardless to its request method, so may try this:
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->any("/", function () {
    return "This is Home! No matter what is the request method!";
});

$router->dispatch();
```

### Array of routes
Other argument which can be an array is `$route`.
You can match some routes (and some request methods) with only one controller.
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->match(array("GET", "POST"), array("/", "/home"), function () {
    return "Homepage for GET, POST and DELETE requests!";
});

$router->dispatch();
```

### Controllers
Personally, I hate using closure as controller.
I believe a controller absolutely must be a method.
However, following codes shows how to use all kind of controllers.
```
use Neatplex\PHPRouter\Router;

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

### Do not forget namespaces!
Because of MVC pattern, developers usually put controller classes in a directory and declare them with namespace.
Current version of Neatplex PHPRouter doesn't recognize used namespaces, so you must pass them with class name.
Check following example which `Post` class is declared with `Blog` namespace.
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->get("/post/{id}", 'Blog\Post@show');

$router->dispatch();
```
The `Post` class:
```
<?php namespace Blog;

class Post {
    function show($id) {
        return "Post " . $id;
    }
}
```

### Dynamic routes
Of course! Router is build to help developers to handle dynamic routes easier than ever.
So let's back to the first example, I mean this one:
```
http://example.com/blog/post/93
```
The post ID (`93` in the example above) is variable.
Actually we need to handle all the following pattern with one controller:
```
http://example.com/blog/post/{id}
```
Don't worry if you use Neatplex PHPRouter!
Because you can handle it in the easiest way.
Look at following example:
```
use Neatplex\PHPRouter\Router;

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

use Neatplex\PHPRouter\Router;

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
If request URI has ID, it returns page ID.
If request URI is without ID (`/page/`), it returns `All pages!`.
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->get("/page/{id?}", function ($id) {
    if($id == null)
        return "All pages!";
    return "Page $id";
});

$router->dispatch();
```
*   All parameters which hasn't value will be `null`.

In the example above to see all pages, user must visit `/page/`.
You may want it to work for `/page` too.
To achieve that you can use array of routes or use following trick:
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->get("/page/?{id?}", function ($id) {
    if($id == null)
        return "All pages!";
    return "Page $id";
});

$router->dispatch();
```

### Customized route parameters with Regular Expression
In default, route parameters can be everything (`[^/]+`).
Sometimes the route parameter must be only a numeric value.
Sometimes you need them to follow a complex pattern.
No problem! You can define Regular Expression pattern for some or all of the route parameters.
```
use Neatplex\PHPRouter\Router;

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
*   No need to check if ID is a number of not, I will be a number, promise!
*   To avoid unwanted results, pattern group characters (`(` and `)`) are disabled.

### Request and Response objects
The Router passes two objects named `$request` and `$response` to the route controller.
Of course the controller must be considered to get them too.
These objects help you in some cases.
To get these object you must name them (one or both) in the controller parameters.
Don't worry, Neatplex PHPRouter is enough flexible to don't care about order of controller parameters!
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->get("/page/{num}", function ($num, $request, $response) {

    return "Page $num<br> User IP:" . $request->ip();

    // All methods in $request
    // $request->ip();
    // $request->port();
    // $request->method();
    // $request->protocol();
    // $request->uri();
    // $request->queryString();
    // $request->get(); // $_GET
    // $request->port(); // $_POST

    // All methods in $response
    // $response->redirect();
    // $response->render(); // include

});

$router->dispatch();
```
If you use IDEs like PhpStorm, it'd be good practice not to omit `$request` and `$response` data types.
Then your IDE can help you and provide auto-complete options.
See example below:
```
use Neatplex\PHPRouter\Router;
use Neatplex\PHPRouter\Request;
use Neatplex\PHPRouter\Response;

$router = new Router();

$router->get("/page/{num}", function ($num, Request $request, Response $response) {
    return "Page $num <br> User IP:" . $request->ip();
});

$router->dispatch();
```

### Access $_GET and $_POST
Of course you can use the same `$_GET` and `$_POST` arrays.
But it might neater and more beautiful to use `get()` and `post()` methods in `$request` object.
Run following example for `http://example.com/user?id=93`:
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->get("/user", function ($request) {
    return $request->get("id");
});

$router->dispatch();
```

### Redirection
You can redirect the request with the php `header()` function.
But if you are interested in using Neatplex PHPRouter methods, you may try this:
```
use Neatplex\PHPRouter\Router;

$router = new Router();

$router->get("/old-page", function ($response) {
    $response->redirect("/new-page");
});

$router->dispatch();
```
*   The `redirect()` method will consider the **Base URI**.

### Render PHP files
Sometimes the result you want to return is not plain texts or compiled PHP codes but it's a PHP file.
No problem! you can use `render()` method in `$response` object as it's shown in the following example:
```
use Neatplex\PHPRouter\Router;

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
use Neatplex\PHPRouter\Router;

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
In this version Neatplex PHPRouter supports common **middleware**, **prefix** and **postfix**.
For example all of user control panel pages need authentication.
So you can group all of them.
```
use Neatplex\PHPRouter\Router;

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
use Neatplex\PHPRouter\Router;
$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

$options = array("middleware" => "authenticate", "prefix" => "/user"); // This line!

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
*   You may put `$router` data type (`Router`) in the group body parameters.
*   If you use this way of injecting Router object to the group body,
    the object's name in the body will be `$router` always.

You may use PHP `use` statement.
It's useful specially when your Router object name is not `$router`.
```
use Neatplex\PHPRouter\Router;

$my_router = new Router();

// Middleware
function authenticate($id, $response)
{
    if (is_permitted_user($id) == false) {
        $response->redirect("/forbidden");
    }
}

$options = array("middleware" => "authenticate", "prefix" => "/user");

$my_router->group($options, function () use ($my_router) { // This line!

    $my_router->get("/{id}/profile", function ($id) {
        return "User $id profile page!";
    });

    // ...

});

$router->dispatch();
```
*   When you use `use` statement, you can keep you Router object's name in the group body.

### Base URI and projects in subdirectory
You can handle routes for the projects in subdirectories with the tools explained above.
But there is a tool to make this job even easier.
For example you work on a blog and all of its files are in directory like `blog`.
You can set it's base URI to route you blog easier.
It's useful in some cases like redirection too, the `redirect()` in `$response` object adds base URI to the target.
```
use Neatplex\PHPRouter\Router;

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
use Neatplex\PHPRouter\Router;
use Neatplex\PHPRouter\HttpError;
use Neatplex\PHPRouter\PHPRouterError;

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
Neatplex PHPRouter doesn't manipulate your application exceptions,
so you can catch them like `HttpError` and `PHPRouterError` exceptions.

### Template Engine
When you use a router for your application, you will find it out soon which you need a **template engine** too.
Template engines usually returns HTML output, so you can return it in your controller.
Next Neatplex package is a template engine, if you cannot wait awhile,
so [Mustache template engine](https://github.com/bobthecow/mustache.php) is a good option.

## Contributors
*	[Milad Rahimi](http://miladrahimi.com)

## License
PHPRouter is created by [Neatplex](http://neatplex.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
