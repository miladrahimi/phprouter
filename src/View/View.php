<?php

namespace MiladRahimi\PhpRouter\View;

/**
 * Interface View
 * It makes views
 *
 * @package MiladRahimi\PhpRouter\Services
 */
interface View
{
    /**
     * Make a view
     *
     * @param string $name
     * @param array $data
     * @param int $httpStatus
     * @param string[] $httpHeaders
     * @return mixed
     */
    public function make(string $name, array $data = [], int $httpStatus = 200, array $httpHeaders = []);
}