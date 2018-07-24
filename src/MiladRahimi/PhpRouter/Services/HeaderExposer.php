<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/10/2018 AD
 * Time: 22:44
 */

namespace MiladRahimi\PhpRouter\Services;

class HeaderExposer implements HeaderExposerInterface
{
    /**
     * @inheritdoc
     */
    public function addHeaderLine(string $name, string $value)
    {
        header($name . ': ' . $value);
    }

    /**
     * @inheritdoc
     */
    public function setResponseCode(int $code)
    {
        http_response_code($code);
    }
}
