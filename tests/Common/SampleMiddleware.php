<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

use Psr\Http\Message\ServerRequestInterface;

class SampleMiddleware
{
    public string $content;

    public static array $output = [];

    public function __construct(?string $content = null)
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
