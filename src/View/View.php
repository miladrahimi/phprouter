<?php

namespace MiladRahimi\PhpRouter\View;

/**
 * It makes views
 */
interface View
{
    /**
     * Make a view
     *
     * @param string[] $httpHeaders
     * @return mixed
     */
    public function make(string $name, array $data = [], int $httpStatus = 200, array $httpHeaders = []);
}