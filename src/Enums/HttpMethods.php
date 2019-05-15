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
    const OPTIONS = 'OPTIONS';
    const HEAD = 'HEAD';
    const CONNECT = 'CONNECT';
    const TRACE = 'TRACE';

    /**
     * Standardize custom http method name
     * For the methods that are not defined in this enum
     *
     * @param string $method
     * @return string
     */
    public static function custom(string $method): string
    {
        return strtoupper($method);
    }
}
