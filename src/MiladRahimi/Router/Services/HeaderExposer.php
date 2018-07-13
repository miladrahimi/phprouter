<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/10/2018 AD
 * Time: 22:44
 */

namespace MiladRahimi\Router\Services;

class HeaderExposer implements HeaderExposerInterface
{
    /**
     * Add header line to http response headers
     *
     * @param string $name
     * @param string $value
     */
    public function addHeaderLine(string $name, string $value)
    {
        header($name . ': ' . $value);
    }
}