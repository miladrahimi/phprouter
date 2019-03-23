<?php

namespace MiladRahimi\PhpRouter\Services;

/**
 * Interface PublisherInterface
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

    /**
     * Set output stream
     *
     * @param string $stream
     */
    public function setStream(string $stream): void;

    /**
     * Get output stream
     *
     * @return string
     */
    public function getStream(): string;
}
