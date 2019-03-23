<?php

namespace MiladRahimi\PhpRouter\Services;

use Psr\Http\Message\ResponseInterface;

/**
 * Class Publisher
 *
 * @package MiladRahimi\PhpRouter\Services
 */
class Publisher implements PublisherInterface
{
    /**
     * @var string
     */
    private $stream;

    /**
     * Publisher constructor.
     *
     * @param string $stream
     */
    public function __construct(string $stream = 'php://output')
    {
        $this->setStream($stream);
    }

    /**
     * @inheritdoc
     */
    public function publish($content): void
    {
        $content = empty($content) ? '' : $content;

        $output = fopen($this->stream, 'a');

        if ($content instanceof ResponseInterface) {
            http_response_code($content->getStatusCode());

            foreach ($content->getHeaders() as $name => $values) {
                $value = $content->getHeaderLine($name);
                header($name.': '.$value);
            }

            fwrite($output, $content->getBody());
        } elseif (is_scalar($content)) {
            fwrite($output, $content);
        } else {
            fwrite($output, json_encode($content));
        }

        fclose($output);
    }

    /**
     * @inheritdoc
     */
    public function getStream(): string
    {
        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function setStream(string $stream): void
    {
        $this->stream = $stream;
    }
}
