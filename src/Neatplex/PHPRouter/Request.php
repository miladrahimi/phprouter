<?php namespace Neatplex\PHPRouter;

    /*
    --------------------------------------------------------------------------------
    Request Class
    --------------------------------------------------------------------------------
    Request Class is used to get user HTTP request information. Whole the project
    would have only one instance (singleton pattern) an that is an optional
    parameter for controller method, function to closure.
    --------------------------------------------------------------------------------
    http://neatplex.com/package/phprouter/master/component#request
    --------------------------------------------------------------------------------
    */

/**
 * Class Request
 *
 * @package Neatplex\PHPRouter
 */
class Request
{

    /**
     * Singleton instance of the class
     *
     * @var Request
     */
    private static $instance = null;

    /**
     * Injected Router object
     *
     * @var Router
     */
    private $router;

    /**
     * @param Router $rooter
     */
    private function __construct(Router $rooter)
    {
        if (!($rooter instanceof Router))
            throw new \InvalidArgumentException("Neatplex PHPRouter: Invalid object given instead of Router object");
        $this->router = $rooter;
    }

    /**
     * Get singleton instance of the class
     *
     * @param Router $router
     * @return Request
     */
    public static function getInstance(Router $router = null)
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new Request($router);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->router->getRequestMethod() . " " . $this->router->getRequestUri();
    }

    /**
     * Return the current user requested URI
     *
     * @return string
     */
    public function uri()
    {
        $u = $this->router->getRequestUri();
        $b = $this->router->getBaseURI();
        if (substr($u, 0, strlen($b)) == $b)
            $u = substr($u, strlen($b));
        return $u;
    }

    /**
     * Return the user HTTP request query string
     *
     * @return string
     */
    public function queryString()
    {
        return $this->router->getRequestQueryString();
    }

    /**
     * Return the user HTTP request method
     *
     * @return string
     */
    public function method()
    {
        return $this->router->getRequestMethod();
    }

    /**
     * Return the user HTTP request protocol
     *
     * @return string
     */
    public function protocol()
    {
        return $this->router->getRequestProtocol();
    }

    /**
     * Return the user HTTP request IP
     *
     * @return string
     */
    public function ip()
    {
        return $this->router->getRequestIP();
    }

    /**
     * Return the user HTTP request port
     *
     * @return int
     */
    public function port()
    {
        return $this->router->getRequestPort();
    }

    /**
     * Return the user HTTP request GET
     *
     * @param string $key
     * @return array
     */
    public function get($key = null)
    {
        if (is_null($key))
            return $this->router->getRequestGET();
        $get = $this->router->getRequestGET();
        return isset($get[$key]) ? $get[$key] : null;
    }

    /**
     * Return the user HTTP request POST
     *
     * @param string $key
     * @return array
     */
    public function post($key = null)
    {
        if (is_null($key))
            return $this->router->getRequestPOST();
        $post = $this->router->getRequestPOST();
        return isset($post[$key]) ? $post[$key] : null;
    }

}