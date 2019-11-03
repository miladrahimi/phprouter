<?php

namespace MiladRahimi\PhpRouter\Examples;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Middleware
 * Sample middleware used in examples
 *
 * @package MiladRahimi\PhpRouter\Examples
 */
class Middleware implements \MiladRahimi\PhpRouter\Middleware
{
    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        $request = $request->withAttribute('color', 'blue');

        return $next($request);
    }
}
