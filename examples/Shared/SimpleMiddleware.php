<?php

namespace MiladRahimi\PhpRouter\Examples\Shared;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

class SimpleMiddleware
{
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        return $next($request);
    }
}
