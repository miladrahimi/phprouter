<?php

namespace MiladRahimi\PhpRouter\Enums;

/**
 * Class HttpMethods
 *
 * @package MiladRahimi\PhpRouter\Enums
 */
class HttpMethods
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';

    /**
     * Standardize given http method name
     *
     * @param string $method
     * @return string
     */
    public static function custom(string $method): string
    {
        return strtoupper($method);
    }
}
