<?php

namespace MiladRahimi\PhpRouter\Tests\Testing;

use Closure;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ServerRequestInterface;

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

        $this->content = $content ?: 'empty';
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
