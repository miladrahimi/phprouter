<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

use Psr\Http\Message\ServerRequestInterface;

class SampleMiddleware
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

    public function handle(ServerRequestInterface $request, $next)
    {
        static::$output[] = $this->content;

        return $next($request);
    }
}
