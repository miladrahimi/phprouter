<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/13/2018 AD
 * Time: 16:23
 */

namespace MiladRahimi\PhpRouter\Services;

use Psr\Http\Message\ResponseInterface;

class Publisher implements PublisherInterface
{
    /**
     * @var string
     */
    private $stream;

    /**
     * @var HeaderExposerInterface
     */
    private $headerExposer;

    /**
     * Publisher constructor.
     */
    public function __construct()
    {
        $this->setStream('php://output');
        $this->headerExposer = new HeaderExposer();
    }

    /**
     * @inheritdoc
     */
    public function publish($content)
    {
        $content = empty($content) ? '' : $content;

        $output = fopen($this->stream, 'r+');

        if ($content instanceof ResponseInterface) {
            $this->headerExposer->setResponseCode($content->getStatusCode());

            foreach ($content->getHeaders() as $name => $values) {
                $value = $content->getHeaderLine($name);
                $this->headerExposer->addHeaderLine($name, $value);
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
     * Set header exposer
     *
     * @param HeaderExposerInterface $headerExposer
     */
    public function setHeaderExposer(HeaderExposerInterface $headerExposer)
    {
        $this->headerExposer = $headerExposer;
    }

    /**
     * Get header exposer
     *
     * @return HeaderExposerInterface
     */
    public function getHeaderExposer(): HeaderExposerInterface
    {
        return $this->headerExposer;
    }

    /**
     * @return string
     */
    public function getStream(): string
    {
        return $this->stream;
    }

    /**
     * @param string $stream
     */
    public function setStream(string $stream)
    {
        $this->stream = $stream;
    }
}
