<?php

namespace MiladRahimi\PhpRouter\Tests\Classes;

use Closure;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SampleMiddleware
 *
 * @package MiladRahimi\PhpRouter\Tests\Classes
 */
class SampleMiddleware implements Middleware
{
    /**
     * @var string
     */
    public $content;

    /**
     * @var array
     */
    public static $output = [];

    /**
     * SampleMiddleware constructor.
     *
     * @param string|null $content
     */
    public function __construct(string $content = null)
    {
        static::$output = [];

        $this->content = $content ?: mt_rand(1, 9999999);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        static::$output[] = $this->content;

        return $next($request);
    }
}
