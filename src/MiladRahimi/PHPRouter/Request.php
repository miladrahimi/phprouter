<?php namespace MiladRahimi\PHPRouter;

use MiladRahimi\PHPRouter\Exceptions\InvalidArgumentException;

/**
 * Class Request
 * Request Class is used to get user HTTP request information. Whole the project
 * would have only one instance (singleton pattern) an that is an optional
 * parameter for controller method, function to closure.
 *
 * @package MiladRahimi\PHPRouter
 * @author Milad Rahimi <info@miladrahimi.com>
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
     * Full URL (Website + URI)
     *
     * @var string
     */
    private $url;

    /**
     * The base URI (for projects in sub-folder)
     *
     * @var string
     */
    private $base_uri;

    /**
     * Current URI (removed base URI and query string)
     *
     * @var string
     */
    private $local_uri;

    /**
     * Current page (removed query string)
     *
     * @var string
     */
    private $page;

    /**
     * End-user request URI
     *
     * @var string
     */
    private $uri;

    /**
     * End-user request query string
     *
     * @var string
     */
    private $query_string;

    /**
     * End-user request method
     *
     * @var string
     */
    private $method;

    /**
     * End-user request protocol
     *
     * @var string
     */
    private $protocol;

    /**
     * End-user request IP
     *
     * @var string
     */
    private $ip;

    /**
     * End-user request server name
     *
     * @var string
     */
    private $website;

    /**
     * End-user request port
     *
     * @var int
     */
    private $port;

    /**
     * End-user request HTTP referer
     *
     * @var string
     */
    private $referer;

    /**
     * End-user request POST array
     *
     * @var array
     */
    private $post;

    /**
     * End-user request GET array
     *
     * @var array
     */
    private $get;

    /**
     * Constructor
     *
     * @param Router $router : Router object
     */
    private function __construct(Router $router)
    {
        $this->router = $router;
        $u = $_SERVER["REQUEST_URI"];
        $this->uri = $u ? urldecode($u) : null;
        $q = $this->query_string = $_SERVER["QUERY_STRING"] ?: null;
        $this->page = trim(substr($u, 0, strlen($u) - strlen($q)), '?');
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->protocol = $_SERVER["SERVER_PROTOCOL"];
        $this->ip = $_SERVER["REMOTE_ADDR"];
        $this->website = $_SERVER["SERVER_NAME"];
        $this->port = $_SERVER["REMOTE_PORT"];
        $this->referer = empty($_SERVER["HTTP_REFERER"]) ? null : $_SERVER["HTTP_REFERER"];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->base_uri = $router->getBaseURI();
        $lu = $this->page;
        if (substr($lu, 0, strlen($this->base_uri)) == $this->base_uri)
            $lu = substr($lu, strlen($this->base_uri));
        $this->local_uri = $lu;
        $this->url = $this->website . $this->uri;
    }

    /**
     * Get singleton instance of the class
     *
     * @param Router $router
     * @return Request
     */
    public static function getInstance(Router $router)
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new Request($router);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->method . " " . $this->uri;
    }

    /**
     * Return the current user requested URI
     *
     * @return string
     */
    public function uri()
    {
        $u = $this->uri;
        $b = $this->router->getBaseURI();
        if (substr($u, 0, strlen($b)) == $b)
            $u = substr($u, strlen($b));
        return $u;
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
            return $this->get;
        $get = $this->get;
        return isset($get[$key]) ? $get[$key] : null;
    }

    /**
     * Return the user HTTP request POST
     *
     * @param string $name
     * @return array
     */
    public function post($name = null)
    {
        if (is_null($name))
            return $this->post;
        if (!is_scalar($name))
            throw new InvalidArgumentException("Name must be a string value");
        return isset($this->post[$name]) ? $this->post[$name] : null;
    }

    /**
     * @return string
     */
    public function getLocalUri()
    {
        return $this->local_uri;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->query_string;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getIP()
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Request cookies (Read-only)
     *
     * @param string $name
     * @return array
     */
    public function cookie($name = null)
    {
        if (is_null($name))
            return $_COOKIE;
        if (!is_scalar($name))
            throw new InvalidArgumentException("Name must be a string value");
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->base_uri;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

}
