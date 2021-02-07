<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

use MiladRahimi\PhpRouter\Publisher\Publisher;
use Psr\Http\Message\ResponseInterface;

class TrapPublisher implements Publisher
{
    /**
     * @var string
     */
    public $output = '';

    /**
     * @var int
     */
    public $responseCode = 0;

    /**
     * @var string[]
     */
    public $headerLines = [];

    /**
     * @inheritdoc
     */
    public function publish($response): void
    {
        $response = empty($response) ? '' : $response;

        if ($response instanceof ResponseInterface) {
            $this->responseCode = $response->getStatusCode();

            foreach ($response->getHeaders() as $name => $values) {
                $value = $response->getHeaderLine($name);
                $this->headerLines[] = $name.': '.$value;
            }

            $this->output = $response->getBody();
        } elseif (is_scalar($response)) {
            $this->output = (string)$response;
        } else {
            $this->output = json_encode($response);
        }
    }
}
