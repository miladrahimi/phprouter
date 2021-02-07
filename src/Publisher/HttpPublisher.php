<?php

namespace MiladRahimi\PhpRouter\Publisher;

use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpPublisher
 * It publishes responses provided by controllers and middleware as HTTP responses
 *
 * @package MiladRahimi\PhpRouter\Services
 */
class HttpPublisher implements Publisher
{
    /**
     * @inheritdoc
     */
    public function publish($response): void
    {
        $output = fopen('php://output', 'a');

        if ($response instanceof ResponseInterface) {
            http_response_code($response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                @header($name . ': ' . $response->getHeaderLine($name));
            }

            fwrite($output, $response->getBody());
        } elseif (is_scalar($response)) {
            fwrite($output, $response);
        } elseif ($response === null) {
            fwrite($output, '');
        } else {
            fwrite($output, json_encode($response));
        }

        fclose($output);
    }
}
