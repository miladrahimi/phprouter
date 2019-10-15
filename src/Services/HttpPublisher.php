<?php

namespace MiladRahimi\PhpRouter\Services;

use Psr\Http\Message\ResponseInterface;

/**
 * Class StreamPublisher
 * StreamPublisher publishes the given resp
 *
 * @package MiladRahimi\PhpRouter\Services
 * @codeCoverageIgnore
 */
class HttpPublisher implements PublisherInterface
{
    /**
     * @inheritdoc
     */
    public function publish($content): void
    {
        $content = empty($content) ? '' : $content;

        $output = fopen('php://output', 'a');

        if ($content instanceof ResponseInterface) {
            http_response_code($content->getStatusCode());

            foreach ($content->getHeaders() as $name => $values) {
                $value = $content->getHeaderLine($name);
                header($name . ': ' . $value);
            }

            fwrite($output, $content->getBody());
        } elseif (is_scalar($content)) {
            fwrite($output, $content);
        } else {
            fwrite($output, json_encode($content));
        }

        fclose($output);
    }
}
