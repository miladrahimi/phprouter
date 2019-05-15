<?php

namespace MiladRahimi\PhpRouter\Services;

/**
 * Interface PublisherInterface
 * Publishers are responsible to publish the response provided by controllers
 *
 * @package MiladRahimi\PhpRouter\Services
 */
interface PublisherInterface
{
    /**
     * Publish the content
     *
     * @param mixed $content
     */
    public function publish($content): void;
}
