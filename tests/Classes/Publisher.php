<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/13/2018 AD
 * Time: 17:07
 */

namespace MiladRahimi\PhpRouter\Tests\Classes;

use MiladRahimi\PhpRouter\Services\PublisherInterface;
use Psr\Http\Message\ResponseInterface;

class Publisher implements PublisherInterface
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
    public function publish($content)
    {
        if ($content instanceof ResponseInterface) {
            $this->responseCode = $content->getStatusCode();

            foreach ($content->getHeaders() as $name => $values) {
                $value = $content->getHeaderLine($name);
                $this->headerLines[] = $name . ': ' . $value;
            }

            $this->output = $content->getBody();
        } elseif (is_scalar($content)) {
            $this->output = (string)$content;
        } else {
            $this->output = json_encode($content);
        }
    }
}