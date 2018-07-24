<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/9/2018 AD
 * Time: 14:39
 */

namespace MiladRahimi\PhpRouter\Enums;

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
     * Standardize the custom http method
     *
     * @param string $method
     * @return string
     */
    public static function custom(string $method): string
    {
        return strtoupper($method);
    }
}
