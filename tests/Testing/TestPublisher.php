<?php

namespace MiladRahimi\PhpRouter\Tests\Testing;

use MiladRahimi\PhpRouter\Services\Publisher;
use Psr\Http\Message\ResponseInterface;

class TestPublisher implements Publisher
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
    public function publish($content): void
    {
        $content = empty($content) ? '' : $content;

        if ($content instanceof ResponseInterface) {
            $this->responseCode = $content->getStatusCode();

            foreach ($content->getHeaders() as $name => $values) {
                $value = $content->getHeaderLine($name);
                $this->headerLines[] = $name.': '.$value;
            }

            $this->output = $content->getBody();
        } elseif (is_scalar($content)) {
            $this->output = (string)$content;
        } else {
            $this->output = json_encode($content);
        }
    }
}
