<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/10/2018 AD
 * Time: 22:43
 */

namespace MiladRahimi\PhpRouter\Services;

interface HeaderExposerInterface
{
    /**
     * Add header line to http response headers
     *
     * @param string $name
     * @param string $value
     */
    public function addHeaderLine(string $name, string $value);

    /**
     * Set http response code
     *
     * @param int $code
     */
    public function setResponseCode(int $code);
}
