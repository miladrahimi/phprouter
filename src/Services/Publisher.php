<?php

namespace MiladRahimi\PhpRouter\Services;

/**
 * Interface Publisher
 * Publishers are responsible to publish the response provided by controllers
 *
 * @package MiladRahimi\PhpRouter\Services
 */
interface Publisher
{
    /**
     * Publish the content
     *
     * @param mixed $content
     */
    public function publish($content): void;
}
