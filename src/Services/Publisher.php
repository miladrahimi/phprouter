<?php

namespace MiladRahimi\PhpRouter\Services;

interface Publisher
{
    /**
     * Publish the content
     *
     * @param mixed $content
     */
    public function publish($content): void;
}
